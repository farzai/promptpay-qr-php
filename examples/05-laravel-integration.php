<?php

/**
 * Example 5: Laravel Integration
 *
 * This example demonstrates how to integrate PromptPay QR codes in Laravel applications.
 * Note: This is a conceptual example. In a real Laravel app, you would use proper routing and controllers.
 */

require __DIR__.'/../vendor/autoload.php';

use Farzai\PromptPay\PromptPay;

echo "=== Laravel Integration Examples ===\n\n";

// ====================
// Example 1: Controller Method
// ====================
echo "1. Laravel Controller Example:\n";
echo "   File: app/Http/Controllers/PaymentController.php\n\n";

echo <<<'PHP'
<?php

namespace App\Http\Controllers;

use Farzai\PromptPay\PromptPay;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    /**
     * Generate PromptPay QR code for payment
     */
    public function generateQrCode(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'recipient' => 'required|numeric|digits_between:10,15',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ]);

        try {
            // Generate QR code
            $result = PromptPay::qrCode(
                $validated['recipient'],
                $validated['amount']
            )->toDataUri('png');

            return response()->json([
                'success' => true,
                'qr_code' => $result->getData(),
                'format' => $result->getFormat()->value,
                'size' => $result->getSize(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Download QR code as file
     */
    public function downloadQrCode(string $recipient, float $amount)
    {
        $tempFile = storage_path('app/temp/qrcode-' . time() . '.png');

        // Ensure temp directory exists
        @mkdir(dirname($tempFile), 0755, true);

        // Generate and save QR code
        $result = PromptPay::qrCode($recipient, $amount)
            ->toFile($tempFile);

        return response()->download($tempFile, 'promptpay-qr.png')
            ->deleteFileAfterSend(true);
    }

    /**
     * Display QR code in view
     */
    public function showPaymentPage(Request $request)
    {
        $recipient = config('payment.promptpay_recipient', '0899999999');
        $amount = $request->input('amount', 100);

        $qrCode = PromptPay::qrCode($recipient, $amount)
            ->toDataUri('png');

        return view('payment.qrcode', [
            'qrCode' => $qrCode->getData(),
            'recipient' => $recipient,
            'amount' => $amount,
        ]);
    }
}

PHP;

echo "\n\n";

// ====================
// Example 2: Routes
// ====================
echo "2. Laravel Routes Example:\n";
echo "   File: routes/web.php\n\n";

echo <<<'PHP'
<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// API endpoint for generating QR code
Route::post('/api/qrcode/generate', [PaymentController::class, 'generateQrCode']);

// Download QR code
Route::get('/qrcode/download/{recipient}/{amount}', [PaymentController::class, 'downloadQrCode']);

// Show payment page
Route::get('/payment', [PaymentController::class, 'showPaymentPage']);

PHP;

echo "\n\n";

// ====================
// Example 3: Blade Template
// ====================
echo "3. Laravel Blade Template Example:\n";
echo "   File: resources/views/payment/qrcode.blade.php\n\n";

echo <<<'BLADE'
<!DOCTYPE html>
<html>
<head>
    <title>PromptPay Payment</title>
    <style>
        .qr-container {
            text-align: center;
            margin: 50px auto;
            max-width: 400px;
        }
        .qr-code {
            border: 2px solid #ddd;
            padding: 20px;
            background: white;
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <h1>Scan to Pay</h1>
        <img src="{{ $qrCode }}" alt="PromptPay QR Code" class="qr-code">

        <div>
            <p><strong>Amount:</strong> {{ number_format($amount, 2) }} THB</p>
            <p><strong>Recipient:</strong> {{ $recipient }}</p>
        </div>

        <form action="{{ url('/payment') }}" method="GET">
            <input type="number" name="amount" placeholder="Enter amount" step="0.01">
            <button type="submit">Update Amount</button>
        </form>
    </div>
</body>
</html>

BLADE;

echo "\n\n";

// ====================
// Example 4: Service Class
// ====================
echo "4. Laravel Service Class Example:\n";
echo "   File: app/Services/PromptPayService.php\n\n";

echo <<<'PHP'
<?php

namespace App\Services;

use Farzai\PromptPay\Exceptions\InvalidAmountException;
use Farzai\PromptPay\Exceptions\InvalidRecipientException;
use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\ValueObjects\OutputResult;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PromptPayService
{
    private string $defaultRecipient;
    private int $cacheMinutes;

    public function __construct()
    {
        $this->defaultRecipient = config('payment.promptpay_recipient');
        $this->cacheMinutes = config('payment.qr_cache_minutes', 60);
    }

    /**
     * Generate QR code with caching
     */
    public function generateQrCode(?string $recipient = null, ?float $amount = null): OutputResult
    {
        $recipient = $recipient ?? $this->defaultRecipient;
        $cacheKey = "promptpay_qr_{$recipient}_{$amount}";

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($recipient, $amount) {
            try {
                $config = QrCodeConfig::create(
                    size: config('payment.qr_size', 300),
                    margin: config('payment.qr_margin', 10)
                );

                return PromptPay::qrCode($recipient, $amount)
                    ->withConfig($config)
                    ->toDataUri('png');
            } catch (InvalidRecipientException | InvalidAmountException $e) {
                Log::error('PromptPay QR generation failed', [
                    'recipient' => $recipient,
                    'amount' => $amount,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Validate and normalize recipient
     */
    public function validateRecipient(string $recipient): bool
    {
        try {
            PromptPay::generate($recipient)->build();
            return true;
        } catch (InvalidRecipientException $e) {
            return false;
        }
    }
}

PHP;

echo "\n\n";

// ====================
// Example 5: Configuration
// ====================
echo "5. Laravel Configuration Example:\n";
echo "   File: config/payment.php\n\n";

echo <<<'PHP'
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PromptPay Configuration
    |--------------------------------------------------------------------------
    */

    'promptpay_recipient' => env('PROMPTPAY_RECIPIENT', '0899999999'),

    'qr_size' => env('PROMPTPAY_QR_SIZE', 300),

    'qr_margin' => env('PROMPTPAY_QR_MARGIN', 10),

    'qr_cache_minutes' => env('PROMPTPAY_CACHE_MINUTES', 60),
];

PHP;

echo "\n\n";

// ====================
// Example 6: .env Configuration
// ====================
echo "6. Environment Variables (.env):\n\n";

echo <<<'ENV'
# PromptPay Settings
PROMPTPAY_RECIPIENT=0899999999
PROMPTPAY_QR_SIZE=300
PROMPTPAY_QR_MARGIN=10
PROMPTPAY_CACHE_MINUTES=60

ENV;

echo "\n\n";

// ====================
// Example 7: API Usage with JavaScript
// ====================
echo "7. Frontend JavaScript Integration:\n\n";

echo <<<'JS'
// Generate QR code via AJAX
async function generateQrCode() {
    const recipient = document.getElementById('recipient').value;
    const amount = document.getElementById('amount').value;

    try {
        const response = await fetch('/api/qrcode/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ recipient, amount })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('qr-display').innerHTML =
                `<img src="${data.qr_code}" alt="QR Code">`;
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Failed to generate QR code:', error);
    }
}

JS;

echo "\n\n✓ Laravel integration examples completed!\n";
echo "Note: These are conceptual examples. Adapt them to your Laravel application structure.\n";

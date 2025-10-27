<?php

/**
 * Example 7: Custom Validation
 *
 * This example demonstrates how to implement custom validation logic
 * for business-specific requirements beyond the built-in validation.
 */

require __DIR__.'/../vendor/autoload.php';

use Farzai\PromptPay\Exceptions\InvalidAmountException;
use Farzai\PromptPay\Exceptions\InvalidRecipientException;
use Farzai\PromptPay\PromptPay;

echo "=== Custom Validation Examples ===\n\n";

// ====================
// Example 1: Recipient Whitelist/Blacklist
// ====================
echo "1. Recipient Whitelist/Blacklist Validation:\n";

class RecipientValidator
{
    private array $whitelist = [];

    private array $blacklist = [];

    public function setWhitelist(array $recipients): self
    {
        $this->whitelist = $recipients;

        return $this;
    }

    public function setBlacklist(array $recipients): self
    {
        $this->blacklist = $recipients;

        return $this;
    }

    public function validate(string $recipient): bool
    {
        // Normalize recipient (remove special characters)
        $normalized = preg_replace('/[^0-9]/', '', $recipient);

        // Check blacklist first
        if (! empty($this->blacklist) && in_array($normalized, $this->blacklist)) {
            throw new InvalidRecipientException("Recipient {$recipient} is blacklisted");
        }

        // Check whitelist if configured
        if (! empty($this->whitelist) && ! in_array($normalized, $this->whitelist)) {
            throw new InvalidRecipientException("Recipient {$recipient} is not in whitelist");
        }

        return true;
    }
}

// Example usage
$validator = new RecipientValidator;
$validator->setWhitelist(['0899999999', '0888888888', '0877777777']);

try {
    $recipient = '0899999999';
    $validator->validate($recipient);
    $qr = PromptPay::qrCode($recipient, 100);
    echo "   ✓ Recipient {$recipient} validated and QR generated\n";
} catch (InvalidRecipientException $e) {
    echo "   ✗ Validation failed: {$e->getMessage()}\n";
}

try {
    $recipient = '0866666666'; // Not in whitelist
    $validator->validate($recipient);
    $qr = PromptPay::qrCode($recipient, 100);
    echo "   ✓ Recipient {$recipient} validated and QR generated\n";
} catch (InvalidRecipientException $e) {
    echo "   ✗ Validation failed: {$e->getMessage()}\n";
}

echo "\n";

// ====================
// Example 2: Business Hours Amount Limits
// ====================
echo "2. Business Hours Amount Limits:\n";

class AmountValidator
{
    private float $businessHoursMax = 50000.00;

    private float $afterHoursMax = 10000.00;

    private float $minAmount = 1.00;

    public function validate(float $amount): bool
    {
        $currentHour = (int) date('H');
        $isBusinessHours = ($currentHour >= 9 && $currentHour < 17);

        $maxAmount = $isBusinessHours ? $this->businessHoursMax : $this->afterHoursMax;

        if ($amount < $this->minAmount) {
            throw new InvalidAmountException(
                sprintf('Amount %.2f THB is below minimum %.2f THB', $amount, $this->minAmount)
            );
        }

        if ($amount > $maxAmount) {
            $timeDescription = $isBusinessHours ? 'business hours' : 'after business hours';
            throw new InvalidAmountException(
                sprintf(
                    'Amount %.2f THB exceeds maximum %.2f THB for %s',
                    $amount,
                    $maxAmount,
                    $timeDescription
                )
            );
        }

        return true;
    }
}

$amountValidator = new AmountValidator;

try {
    $amount = 5000.00;
    $amountValidator->validate($amount);
    echo "   ✓ Amount {$amount} THB validated\n";
} catch (InvalidAmountException $e) {
    echo "   ✗ Validation failed: {$e->getMessage()}\n";
}

echo "\n";

// ====================
// Example 3: Daily Transaction Limits
// ====================
echo "3. Daily Transaction Limits:\n";

class TransactionLimitValidator
{
    private array $dailyTotals = [];

    private float $dailyLimit = 100000.00;

    private int $dailyCountLimit = 50;

    public function validate(string $recipient, float $amount): bool
    {
        $today = date('Y-m-d');
        $key = "{$today}:{$recipient}";

        if (! isset($this->dailyTotals[$key])) {
            $this->dailyTotals[$key] = ['total' => 0, 'count' => 0];
        }

        $newTotal = $this->dailyTotals[$key]['total'] + $amount;
        $newCount = $this->dailyTotals[$key]['count'] + 1;

        if ($newTotal > $this->dailyLimit) {
            throw new InvalidAmountException(
                sprintf(
                    'Daily limit exceeded. Current: %.2f THB, Requested: %.2f THB, Limit: %.2f THB',
                    $this->dailyTotals[$key]['total'],
                    $amount,
                    $this->dailyLimit
                )
            );
        }

        if ($newCount > $this->dailyCountLimit) {
            throw new InvalidAmountException(
                sprintf(
                    'Daily transaction count limit exceeded. Limit: %d transactions',
                    $this->dailyCountLimit
                )
            );
        }

        // Update totals
        $this->dailyTotals[$key]['total'] = $newTotal;
        $this->dailyTotals[$key]['count'] = $newCount;

        return true;
    }

    public function getDailyTotal(string $recipient): array
    {
        $today = date('Y-m-d');
        $key = "{$today}:{$recipient}";

        return $this->dailyTotals[$key] ?? ['total' => 0, 'count' => 0];
    }
}

$limitValidator = new TransactionLimitValidator;
$testRecipient = '0899999999';

// Simulate multiple transactions
$amounts = [10000, 15000, 20000, 25000, 30000];

foreach ($amounts as $amount) {
    try {
        $limitValidator->validate($testRecipient, $amount);
        $qr = PromptPay::qrCode($testRecipient, $amount);
        echo "   ✓ Transaction approved: {$amount} THB\n";
    } catch (InvalidAmountException $e) {
        echo "   ✗ Transaction rejected: {$e->getMessage()}\n";
    }
}

$totals = $limitValidator->getDailyTotal($testRecipient);
echo "   Daily totals: {$totals['total']} THB, {$totals['count']} transactions\n\n";

// ====================
// Example 4: Composite Validator
// ====================
echo "4. Composite Validator Pattern:\n";

interface ValidatorInterface
{
    public function validate(string $recipient, ?float $amount = null): bool;
}

class RecipientFormatValidator implements ValidatorInterface
{
    public function validate(string $recipient, ?float $amount = null): bool
    {
        // Use built-in validation
        try {
            PromptPay::generate($recipient)->build();

            return true;
        } catch (InvalidRecipientException $e) {
            throw $e;
        }
    }
}

class RecipientPrefixValidator implements ValidatorInterface
{
    private array $allowedPrefixes = ['08', '09', '06']; // Phone prefixes

    public function validate(string $recipient, ?float $amount = null): bool
    {
        $normalized = preg_replace('/[^0-9]/', '', $recipient);

        if (strlen($normalized) === 10) {
            $prefix = substr($normalized, 0, 2);
            if (! in_array($prefix, $this->allowedPrefixes)) {
                throw new InvalidRecipientException(
                    sprintf(
                        'Phone number prefix "%s" not allowed. Allowed prefixes: %s',
                        $prefix,
                        implode(', ', $this->allowedPrefixes)
                    )
                );
            }
        }

        return true;
    }
}

class CompositeValidator implements ValidatorInterface
{
    private array $validators = [];

    public function addValidator(ValidatorInterface $validator): self
    {
        $this->validators[] = $validator;

        return $this;
    }

    public function validate(string $recipient, ?float $amount = null): bool
    {
        foreach ($this->validators as $validator) {
            $validator->validate($recipient, $amount);
        }

        return true;
    }
}

// Create composite validator
$compositeValidator = new CompositeValidator;
$compositeValidator
    ->addValidator(new RecipientFormatValidator)
    ->addValidator(new RecipientPrefixValidator);

// Test with valid recipient
try {
    $recipient = '0899999999';
    $compositeValidator->validate($recipient);
    $qr = PromptPay::qrCode($recipient, 100);
    echo "   ✓ Recipient {$recipient} passed all validations\n";
} catch (InvalidRecipientException $e) {
    echo "   ✗ Validation failed: {$e->getMessage()}\n";
}

// Test with invalid prefix
try {
    $recipient = '0199999999'; // Invalid prefix
    $compositeValidator->validate($recipient);
    $qr = PromptPay::qrCode($recipient, 100);
    echo "   ✓ Recipient {$recipient} passed all validations\n";
} catch (InvalidRecipientException $e) {
    echo "   ✗ Validation failed: {$e->getMessage()}\n";
}

echo "\n";

// ====================
// Example 5: Custom Exception with Context
// ====================
echo "5. Custom Validation Exception with Context:\n";

class BusinessValidationException extends \Exception
{
    private array $context = [];

    public function __construct(string $message, array $context = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}

class BusinessRuleValidator
{
    public function validate(string $recipient, float $amount): bool
    {
        $errors = [];

        // Rule 1: No transactions on weekends
        if (date('N') >= 6) {
            $errors[] = 'Transactions not allowed on weekends';
        }

        // Rule 2: Minimum amount for Tax IDs
        $normalized = preg_replace('/[^0-9]/', '', $recipient);
        if (strlen($normalized) === 13 && $amount < 100) {
            $errors[] = 'Minimum amount for Tax ID is 100 THB';
        }

        // Rule 3: Maximum per transaction
        if ($amount > 50000) {
            $errors[] = 'Maximum amount per transaction is 50,000 THB';
        }

        if (! empty($errors)) {
            throw new BusinessValidationException(
                'Business validation failed',
                [
                    'recipient' => $recipient,
                    'amount' => $amount,
                    'errors' => $errors,
                    'timestamp' => date('Y-m-d H:i:s'),
                ]
            );
        }

        return true;
    }
}

$businessValidator = new BusinessRuleValidator;

try {
    $recipient = '1234567890123'; // Tax ID
    $amount = 50.00; // Below minimum for Tax ID
    $businessValidator->validate($recipient, $amount);
    echo "   ✓ Business rules validated\n";
} catch (BusinessValidationException $e) {
    echo "   ✗ Business validation failed: {$e->getMessage()}\n";
    echo "   Context:\n";
    foreach ($e->getContext() as $key => $value) {
        if (is_array($value)) {
            echo "     {$key}:\n";
            foreach ($value as $item) {
                echo "       - {$item}\n";
            }
        } else {
            echo "     {$key}: {$value}\n";
        }
    }
}

echo "\n";

// ====================
// Example 6: Validation Chain with Fluent Interface
// ====================
echo "6. Fluent Validation Chain:\n";

class ValidationChain
{
    private string $recipient;

    private ?float $amount;

    private array $errors = [];

    public function __construct(string $recipient, ?float $amount = null)
    {
        $this->recipient = $recipient;
        $this->amount = $amount;
    }

    public function isNotEmpty(): self
    {
        if (empty($this->recipient)) {
            $this->errors[] = 'Recipient cannot be empty';
        }

        return $this;
    }

    public function isNumeric(): self
    {
        if (! preg_match('/^[0-9]+$/', preg_replace('/[^0-9]/', '', $this->recipient))) {
            $this->errors[] = 'Recipient must be numeric';
        }

        return $this;
    }

    public function hasValidLength(): self
    {
        $normalized = preg_replace('/[^0-9]/', '', $this->recipient);
        $length = strlen($normalized);
        if (! in_array($length, [10, 13, 15])) {
            $this->errors[] = "Invalid length: {$length} digits";
        }

        return $this;
    }

    public function amountInRange(float $min, float $max): self
    {
        if ($this->amount !== null) {
            if ($this->amount < $min || $this->amount > $max) {
                $this->errors[] = sprintf('Amount must be between %.2f and %.2f THB', $min, $max);
            }
        }

        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function validate(): bool
    {
        if (! $this->isValid()) {
            throw new BusinessValidationException(
                'Validation failed',
                ['errors' => $this->errors]
            );
        }

        return true;
    }
}

// Example usage
try {
    (new ValidationChain('0899999999', 150.00))
        ->isNotEmpty()
        ->isNumeric()
        ->hasValidLength()
        ->amountInRange(1.00, 50000.00)
        ->validate();

    echo "   ✓ All validations passed\n";
} catch (BusinessValidationException $e) {
    echo "   ✗ Validation failed:\n";
    foreach ($e->getContext()['errors'] as $error) {
        echo "     - {$error}\n";
    }
}

echo "\n✓ All custom validation examples completed!\n";
echo "Note: Combine these patterns to create validation rules specific to your business needs.\n";

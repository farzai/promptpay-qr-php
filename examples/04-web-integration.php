<?php

/**
 * Example 4: Web Integration
 *
 * This example shows how to integrate PromptPay QR codes in web applications.
 */

require __DIR__.'/../vendor/autoload.php';

use Farzai\PromptPay\PromptPay;

// Simulate web request parameters
$recipient = $_GET['recipient'] ?? '0899999999';
$amount = $_GET['amount'] ?? 100;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PromptPay QR Code Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .qr-container {
            text-align: center;
            margin: 30px 0;
            padding: 30px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .qr-container img {
            max-width: 300px;
            border: 2px solid #ddd;
            padding: 10px;
            background: white;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .form-group {
            margin: 15px 0;
        }
        input {
            padding: 10px;
            width: 200px;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <h1>PromptPay QR Code Generator</h1>

    <form method="GET">
        <div class="form-group">
            <label>Recipient (Phone/Tax ID):</label><br>
            <input type="text" name="recipient" value="<?= htmlspecialchars($recipient) ?>" placeholder="0899999999">
        </div>

        <div class="form-group">
            <label>Amount (THB):</label><br>
            <input type="number" name="amount" value="<?= htmlspecialchars((string) $amount) ?>" step="0.01" placeholder="100.00">
        </div>

        <button type="submit">Generate QR Code</button>
    </form>

    <?php
    try {
        // Generate QR code
        $result = PromptPay::qrCode($recipient, (float) $amount)
            ->toDataUri('png');
        ?>

        <div class="qr-container">
            <h2>Your PromptPay QR Code</h2>
            <img src="<?= $result->getData() ?>" alt="PromptPay QR Code">

            <div class="info">
                <strong>Recipient:</strong> <?= htmlspecialchars($recipient) ?><br>
                <strong>Amount:</strong> <?= number_format((float) $amount, 2) ?> THB<br>
                <strong>Size:</strong> <?= number_format($result->getSize()) ?> bytes<br>
                <strong>Format:</strong> <?= $result->getFormat()->value ?>
            </div>

            <p>
                <small>Scan this QR code with any PromptPay-enabled banking app</small>
            </p>
        </div>

    <?php
    } catch (\Exception $e) {
        ?>
        <div style="background: #ffebee; padding: 15px; border-radius: 5px; color: #c62828;">
            <strong>Error:</strong> <?= htmlspecialchars($e->getMessage()) ?>
        </div>
        <?php
    }
?>

    <hr>

    <h3>Example Usage:</h3>
    <pre><code>&lt;?php
use Farzai\PromptPay\PromptPay;

// Generate QR code
$result = PromptPay::qrCode('0899999999', 100)
    ->toDataUri('png');

// Display in HTML
echo '&lt;img src="' . $result->getData() . '" /&gt;';
?&gt;</code></pre>
</body>
</html>

<?php

/**
 * Example 1: Basic Usage
 *
 * This example demonstrates the simplest way to generate a PromptPay QR code.
 */

require __DIR__ . '/../vendor/autoload.php';

use Farzai\PromptPay\PromptPay;

echo "=== Basic PromptPay QR Code Generation ===\n\n";

// Example 1: Static QR Code (no amount)
echo "1. Static QR Code (customer enters amount):\n";
$qrCode = PromptPay::create('0899999999');
echo "Payload: " . $qrCode . "\n\n";

// Example 2: Dynamic QR Code (with amount)
echo "2. Dynamic QR Code (amount pre-filled):\n";
$qrCode = PromptPay::create('0899999999', 100.50);
echo "Payload: " . $qrCode . "\n\n";

// Example 3: Using the new builder API
echo "3. Using modern builder pattern:\n";
$result = PromptPay::generate('0899999999')
    ->withAmount(250.75)
    ->toDataUri('png');

echo "Data URI generated\n";
echo "Format: " . $result->getFormat()->value . "\n";
echo "Size: " . $result->getSize() . " bytes\n";
echo "Is Data URI: " . ($result->isDataUri() ? 'Yes' : 'No') . "\n\n";

// Example 4: Different recipient types
echo "4. Different recipient types:\n";

// Phone number (10 digits)
$phone = PromptPay::qrCode('0899999999', 100);
echo "Phone: " . $phone->toPayload() . "\n";

// Tax ID (13 digits)
$taxId = PromptPay::qrCode('1234567890123', 100);
echo "Tax ID: " . $taxId->toPayload() . "\n";

// E-Wallet ID (15 digits)
$ewallet = PromptPay::qrCode('123456789012345', 100);
echo "E-Wallet: " . $ewallet->toPayload() . "\n\n";

echo "✓ All examples completed successfully!\n";

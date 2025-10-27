<?php

/**
 * Example 3: Error Handling
 *
 * This example demonstrates comprehensive error handling with detailed error messages.
 */

require __DIR__ . '/../vendor/autoload.php';

use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\Exceptions\InvalidRecipientException;
use Farzai\PromptPay\Exceptions\InvalidAmountException;

echo "=== Error Handling Examples ===\n\n";

// Example 1: Invalid recipient (too short)
echo "1. Testing invalid recipient (too short):\n";
try {
    PromptPay::qrCode('12345', 100)->build();
} catch (InvalidRecipientException $e) {
    echo "   ✗ Error caught!\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   Message: " . $e->getMessage() . "\n\n";
}

// Example 2: Empty recipient
echo "2. Testing empty recipient:\n";
try {
    PromptPay::qrCode('', 100)->build();
} catch (InvalidRecipientException $e) {
    echo "   ✗ Error caught!\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   Message: " . $e->getMessage() . "\n\n";
}

// Example 3: Negative amount
echo "3. Testing negative amount:\n";
try {
    PromptPay::qrCode('0899999999', -50)->build();
} catch (InvalidAmountException $e) {
    echo "   ✗ Error caught!\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   Message: " . $e->getMessage() . "\n\n";
}

// Example 4: Amount too large
echo "4. Testing amount too large:\n";
try {
    PromptPay::qrCode('0899999999', 9999999999.99)->build();
} catch (InvalidAmountException $e) {
    echo "   ✗ Error caught!\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   Message: " . $e->getMessage() . "\n\n";
}

// Example 5: Handling with error codes
echo "5. Programmatic error handling with error codes:\n";
try {
    PromptPay::qrCode('abc', 100)->build();
} catch (InvalidRecipientException $e) {
    // Handle specific error types
    switch ($e->getCode()) {
        case InvalidRecipientException::CODE_EMPTY:
            echo "   → User needs to provide a recipient\n";
            break;
        case InvalidRecipientException::CODE_NOT_NUMERIC:
            echo "   → Recipient must be numeric\n";
            break;
        case InvalidRecipientException::CODE_INVALID_LENGTH:
            echo "   → Recipient has wrong length\n";
            break;
        case InvalidRecipientException::CODE_EMPTY_AFTER_NORMALIZATION:
            echo "   → No valid digits found in recipient\n";
            break;
        default:
            echo "   → Unknown error\n";
    }
    echo "   Full message: " . $e->getMessage() . "\n\n";
}

// Example 6: Graceful degradation
echo "6. Graceful error recovery:\n";
$inputs = ['0899999999', 'invalid', '0888888888'];

foreach ($inputs as $input) {
    try {
        $qr = PromptPay::qrCode($input, 100);
        echo "   ✓ Successfully created QR for: $input\n";
    } catch (InvalidRecipientException $e) {
        echo "   ✗ Failed for '$input': {$e->getMessage()}\n";
        echo "   → Skipping and continuing...\n";
    }
}

echo "\n✓ All error handling examples completed!\n";

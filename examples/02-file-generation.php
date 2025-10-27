<?php

/**
 * Example 2: File Generation
 *
 * This example shows how to save QR codes to various file formats.
 */

require __DIR__ . '/../vendor/autoload.php';

use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;

echo "=== File Generation Examples ===\n\n";

// Create output directory
$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Example 1: Save as PNG
echo "1. Saving as PNG...\n";
$result = PromptPay::qrCode('0899999999', 100)
    ->toFile($outputDir . '/qrcode.png');

echo "   Saved to: " . $result->getPath() . "\n";
echo "   File size: " . $result->getSize() . " bytes\n";
echo "   Is file: " . ($result->isFile() ? 'Yes' : 'No') . "\n\n";

// Example 2: Save as SVG
echo "2. Saving as SVG...\n";
$result = PromptPay::qrCode('0899999999', 100)
    ->toFile($outputDir . '/qrcode.svg');

echo "   Saved to: " . $result->getPath() . "\n";
echo "   File size: " . $result->getSize() . " bytes\n\n";

// Example 3: Custom size QR code
echo "3. Custom size QR code (600x600)...\n";
$config = QrCodeConfig::create(
    size: 600,
    margin: 20
);

$result = PromptPay::generate('0899999999')
    ->withAmount(150)
    ->withConfig($config)
    ->toFile($outputDir . '/qrcode-large.png');

echo "   Saved to: " . $result->getPath() . "\n";
echo "   File size: " . $result->getSize() . " bytes\n\n";

// Example 4: Multiple QR codes
echo "4. Generating multiple QR codes...\n";
$recipients = [
    ['phone' => '0899999999', 'amount' => 100],
    ['phone' => '0888888888', 'amount' => 200],
    ['phone' => '0877777777', 'amount' => 300],
];

foreach ($recipients as $index => $data) {
    $filename = $outputDir . '/qrcode-' . ($index + 1) . '.png';

    $result = PromptPay::qrCode($data['phone'], $data['amount'])
        ->toFile($filename);

    echo "   Generated: {$filename} ({$result->getSize()} bytes)\n";
}

echo "\n✓ All files generated successfully in: $outputDir\n";
echo "Note: Don't forget to add 'examples/output' to .gitignore\n";

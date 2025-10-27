<?php

use Farzai\PromptPay\Commands\CreateQrCode;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;

beforeEach(function () {
    $this->application = new Application;
    $this->application->add(
        $this->command = new CreateQrCode
    );
    $this->application->setAutoExit(false);

    $this->tester = new ApplicationTester($this->application);
});

it('can generate qr code', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '0899999999',
    ]);

    $display = $this->tester->getDisplay();

    // Check for section headers
    expect($display)->toContain('PromptPay QR Code Generation');

    // Check for recipient information in table format
    expect($display)->toContain('089-999-9999');
    expect($display)->toContain('Phone Number');
});

it('should see amount if amount is not null', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '0899999999',
        'amount' => 100,
    ]);

    $display = $this->tester->getDisplay();

    // Check for recipient and amount information
    expect($display)->toContain('089-999-9999');
    expect($display)->toContain('Phone Number');
    expect($display)->toContain('100.00 THB');
});

it('should ask target when target is null', function () {
    $this->tester->setInputs(['0899999999']);

    $this->tester->run([
        'command' => $this->command->getName(),
    ]);

    expect($this->tester->getDisplay())->toContain(
        <<<'EOF'
Enter Target (phone number, tax ID, or e-wallet ID):
EOF
    );
});

it('should error when target is null after answer target with empty', function () {
    $this->tester->setInputs(['']);

    $this->tester->run([
        'command' => $this->command->getName(),
    ]);

    $display = $this->tester->getDisplay();

    // Check for new error format
    expect($display)->toContain('Error: Missing recipient information');
    expect($display)->toContain('Please provide a valid recipient');
});

it('should handle invalid recipient error', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '123', // Too short
    ]);

    $display = $this->tester->getDisplay();

    expect($display)->toContain('Error: Invalid Recipient');
    expect($display)->toContain('Suggestions:');
});

it('should handle invalid amount error', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '0899999999',
        'amount' => -100, // Negative amount
    ]);

    $display = $this->tester->getDisplay();

    expect($display)->toContain('Error: Invalid Amount');
    expect($display)->toContain('Suggestions:');
});

it('should generate qr code with custom size', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '0899999999',
        '--size' => '500',
    ]);

    $display = $this->tester->getDisplay();

    expect($display)->toContain('500 × 500 pixels');
});

it('should save qr code to file', function () {
    $tempFile = sys_get_temp_dir().'/test-qr-'.uniqid().'.png';

    try {
        $this->tester->run([
            'command' => $this->command->getName(),
            'target' => '0899999999',
            '--output' => $tempFile,
        ]);

        $display = $this->tester->getDisplay();

        expect($display)->toContain('QR Code Generated Successfully');
        expect($display)->toContain('Success');
        expect(file_exists($tempFile))->toBeTrue();
    } finally {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
});

it('should display payload when --show-payload option is used', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '0899999999',
        '--show-payload' => true,
    ]);

    $display = $this->tester->getDisplay();

    expect($display)->toContain('Raw PromptPay Payload');
});

it('should handle tax id recipient type', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '1234567890123', // 13 digits - Tax ID
    ]);

    $display = $this->tester->getDisplay();

    expect($display)->toContain('Tax ID / National ID');
    expect($display)->toContain('1-2345-67890-12-3');
});

it('should handle e-wallet recipient type', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '123456789012345', // 15 digits - E-Wallet
    ]);

    $display = $this->tester->getDisplay();

    expect($display)->toContain('E-Wallet ID');
});

it('should display integration tips when saving to file', function () {
    $tempFile = sys_get_temp_dir().'/test-qr-'.uniqid().'.png';

    try {
        $this->tester->run([
            'command' => $this->command->getName(),
            'target' => '0899999999',
            '--output' => $tempFile,
        ]);

        $display = $this->tester->getDisplay();

        expect($display)->toContain('How to use this QR code:');
        expect($display)->toContain('Print it on receipts');
    } finally {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
});

it('should display console tips when not saving to file', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '0899999999',
    ]);

    $display = $this->tester->getDisplay();

    expect($display)->toContain('How to test this QR code:');
});

it('should display dynamic qr tips with amount', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '0899999999',
        'amount' => 100,
    ]);

    $display = $this->tester->getDisplay();

    expect($display)->toContain('Tips for Dynamic QR Codes');
    expect($display)->toContain('Fixed Amount');
});

it('should display static qr tips without amount', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '0899999999',
    ]);

    $display = $this->tester->getDisplay();

    expect($display)->toContain('Tips for Static QR Codes');
    expect($display)->toContain('Flexible Amount');
});

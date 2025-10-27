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

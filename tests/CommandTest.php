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

    expect($this->tester->getDisplay())->toContain(
        <<<'EOF'
QR Code PromptPay for: 0899999999
====================================
EOF
    );
});

it('should see amount if amount is not null', function () {
    $this->tester->run([
        'command' => $this->command->getName(),
        'target' => '0899999999',
        'amount' => 100,
    ]);

    expect($this->tester->getDisplay())->toContain(
        <<<'EOF'
QR Code PromptPay for: 0899999999
Amount: 100.00
EOF
    );
});

it('should ask target when target is null', function () {
    $this->tester->setInputs(['0899999999']);

    $this->tester->run([
        'command' => $this->command->getName(),
    ]);

    expect($this->tester->getDisplay())->toContain(
        <<<'EOF'
Enter Target (phone number, citizen id, e-wallet id):
EOF
    );
});

it('should error when target is null after answer target with empty', function () {
    $this->tester->setInputs(['']);

    $this->tester->run([
        'command' => $this->command->getName(),
    ]);

    expect($this->tester->getDisplay())->toContain(
        <<<'EOF'
Please enter receiver target., e.g. 0899999999
EOF
    );
});

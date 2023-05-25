<?php

use Farzai\PromptPay\Commands\CreateQrCode;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;

it('can generate qr code', function () {
    $application = new Application();
    $application->add(
        $command = new CreateQrCode()
    );
    $application->setAutoExit(false);

    $tester = new ApplicationTester($application);

    $tester->run([
        'command' => $command->getName(),
        'target' => '0899999999',
    ]);

    expect($tester->getDisplay())->toContain(
        <<<'EOF'
QR Code PromptPay for: 0899999999
====================================
EOF
    );
});

it('should see amount if amount is not null', function () {
    $application = new Application();
    $application->add(
        $command = new CreateQrCode()
    );
    $application->setAutoExit(false);

    $tester = new ApplicationTester($application);

    $tester->run([
        'command' => $command->getName(),
        'target' => '0899999999',
        '--amount' => 100,
    ]);

    expect($tester->getDisplay())->toContain(
        <<<'EOF'
QR Code PromptPay for: 0899999999
Amount: 100.00
EOF
    );
});

it('should ask target when target is null', function () {
    $application = new Application();
    $application->add(
        $command = new CreateQrCode()
    );
    $application->setAutoExit(false);

    $tester = new ApplicationTester($application);

    $tester->setInputs(['0899999999']);

    $tester->run([
        'command' => $command->getName(),
    ]);

    expect($tester->getDisplay())->toContain(
        <<<'EOF'
Enter Target (phone number, citizen id, e-wallet id):
EOF
    );
});

it('should error when target is null after answer target with empty', function () {
    $application = new Application();
    $application->add(
        $command = new CreateQrCode()
    );
    $application->setAutoExit(false);

    $tester = new ApplicationTester($application);

    $tester->setInputs(['']);

    $tester->run([
        'command' => $command->getName(),
    ]);

    expect($tester->getDisplay())->toContain(
        <<<'EOF'
Please enter receiver target., e.g. 0899999999
EOF
    );
});

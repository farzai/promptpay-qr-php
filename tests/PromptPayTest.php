<?php

use Farzai\PromptPay\Contracts\QrCode as QrCodeContract;
use Farzai\PromptPay\PromptPay;

it('should create qr code with phone number', function () {
    $qrCode = PromptPay::create('0899999999', 100);

    expect($qrCode)->toBeInstanceOf(QrCodeContract::class);

    expect((string) $qrCode)->toBe('00020101021229370016A000000677010111011300668999999995802TH53037645406100.006304CB89');
});

it('should create qr code and display data uri', function () {
    $dataUri = PromptPay::to('0899999999')
        ->amount(100)
        ->toDataUri('png');

    $qrCode = PromptPay::create('0899999999', 100);

    $png = $qrCode->writeTo(new \Farzai\PromptPay\Outputs\DataUriOutput('png'));

    expect($dataUri)->toBe($png);
});

it('should create qr code and write to file', function () {
    $filepath = 'qr.png';

    PromptPay::to('0899999999')
        ->amount(100)
        ->toFile($filepath);

    expect(file_exists($filepath))->toBeTrue();

    unlink($filepath);
});

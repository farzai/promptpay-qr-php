<?php

use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\PromptPayBuilder;
use Farzai\PromptPay\ValueObjects\OutputResult;

it('can use new generate() method with builder pattern', function () {
    $builder = PromptPay::generate('0899999999');

    expect($builder)->toBeInstanceOf(PromptPayBuilder::class);
});

it('can chain withAmount() in builder pattern', function () {
    $builder = PromptPay::generate('0899999999')
        ->withAmount(100.50);

    expect($builder)->toBeInstanceOf(PromptPayBuilder::class);
    expect($builder->getAmount())->toBe(100.50);
});

it('can use qrCode() shorthand with amount', function () {
    $builder = PromptPay::qrCode('0899999999', 150);

    expect($builder)->toBeInstanceOf(PromptPayBuilder::class);
    expect($builder->getRecipient())->toBe('0899999999');
    expect($builder->getAmount())->toBe(150);
});

it('builder is immutable when changing amount', function () {
    $builder1 = PromptPay::generate('0899999999')->withAmount(100);
    $builder2 = $builder1->withAmount(200);

    expect($builder1->getAmount())->toBe(100);
    expect($builder2->getAmount())->toBe(200);
    expect($builder1)->not->toBe($builder2);
});

it('can generate QR code using new builder API', function () {
    $qrCode = PromptPay::generate('0899999999')
        ->withAmount(100)
        ->build();

    expect((string) $qrCode)->toBe('00020101021229370016A000000677010111011300668999999995802TH53037645406100.006304CB89');
});

it('can get data uri with new builder API', function () {
    $result = PromptPay::generate('0899999999')
        ->withAmount(100)
        ->toDataUri('png');

    expect($result)->toBeInstanceOf(OutputResult::class);
    expect($result->getFormat())->toBeInstanceOf(\Farzai\PromptPay\Enums\QrFormat::class);
    expect($result->isDataUri())->toBeTrue();
});

it('can save to file with new builder API', function () {
    $filename = 'test-builder-qr.png';

    $result = PromptPay::qrCode('0899999999', 100)
        ->toFile($filename);

    expect($result)->toBeInstanceOf(OutputResult::class);
    expect($result->isFile())->toBeTrue();
    expect($result->getPath())->toBe($filename);
    expect(file_exists($filename))->toBeTrue();

    unlink($filename);
});

it('can get payload as string with new builder API', function () {
    $payload = PromptPay::generate('0899999999')
        ->withAmount(100)
        ->toPayload();

    expect($payload)->toBe('00020101021229370016A000000677010111011300668999999995802TH53037645406100.006304CB89');
});

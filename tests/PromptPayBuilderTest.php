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

it('can use withConfig() method', function () {
    $config = \Farzai\PromptPay\ValueObjects\QrCodeConfig::create(size: 400);
    $builder = PromptPay::generate('0899999999')->withConfig($config);

    expect($builder)->toBeInstanceOf(\Farzai\PromptPay\PromptPayBuilder::class);
});

it('withConfig() returns new instance (immutability)', function () {
    $config1 = \Farzai\PromptPay\ValueObjects\QrCodeConfig::create(size: 300);
    $config2 = \Farzai\PromptPay\ValueObjects\QrCodeConfig::create(size: 500);

    $builder1 = PromptPay::generate('0899999999')->withConfig($config1);
    $builder2 = $builder1->withConfig($config2);

    expect($builder1)->not->toBe($builder2);
});

it('can use toResponse() method with PSR-17 factories', function () {
    $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory;

    $response = PromptPay::generate('0899999999')
        ->toResponse($psr17Factory, $psr17Factory);

    expect($response)->toBeInstanceOf(\Psr\Http\Message\ResponseInterface::class);
    expect($response->getStatusCode())->toBe(200);
});

it('can use toConsole() method', function () {
    $output = new \Symfony\Component\Console\Output\BufferedOutput;

    $result = PromptPay::generate('0899999999')->toConsole($output);

    expect($result)->toBeString();
    expect($output->fetch())->toContain('█');
});

it('getRecipient() returns the recipient', function () {
    $builder = PromptPay::generate('0899999999');

    expect($builder->getRecipient())->toBe('0899999999');
});

it('getAmount() returns null when no amount set', function () {
    $builder = PromptPay::generate('0899999999');

    expect($builder->getAmount())->toBeNull();
});

it('can chain multiple methods', function () {
    $config = \Farzai\PromptPay\ValueObjects\QrCodeConfig::create(size: 500);

    $qrCode = PromptPay::generate('0899999999')
        ->withAmount(150)
        ->withConfig($config)
        ->build();

    expect((string) $qrCode)->toBeString();
    expect((string) $qrCode)->toStartWith('0002010102');
});

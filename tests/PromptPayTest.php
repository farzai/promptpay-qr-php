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

    $factory = \Farzai\PromptPay\Factories\OutputFactory::create();
    $output = $factory->createDataUriOutput('png');
    $png = $qrCode->writeTo($output);

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

it('can use to() method for fluent interface', function () {
    $promptPay = PromptPay::to('0899999999');

    expect($promptPay)->toBeInstanceOf(PromptPay::class);
});

it('can chain amount() method', function () {
    $promptPay = PromptPay::to('0899999999')->amount(100);

    expect($promptPay)->toBeInstanceOf(PromptPay::class);
});

it('amount() method returns new instance (immutability)', function () {
    $promptPay1 = PromptPay::to('0899999999')->amount(100);
    $promptPay2 = $promptPay1->amount(200);

    expect($promptPay1)->not->toBe($promptPay2);
});

it('can use config() method with custom configuration', function () {
    $config = \Farzai\PromptPay\ValueObjects\QrCodeConfig::create(size: 400);
    $promptPay = PromptPay::to('0899999999')->config($config);

    expect($promptPay)->toBeInstanceOf(PromptPay::class);
});

it('config() method returns new instance (immutability)', function () {
    $config1 = \Farzai\PromptPay\ValueObjects\QrCodeConfig::create(size: 300);
    $config2 = \Farzai\PromptPay\ValueObjects\QrCodeConfig::create(size: 500);

    $promptPay1 = PromptPay::to('0899999999')->config($config1);
    $promptPay2 = $promptPay1->config($config2);

    expect($promptPay1)->not->toBe($promptPay2);
});

it('respond() throws exception without PSR-17 factories', function () {
    $promptPay = PromptPay::to('0899999999');

    $promptPay->respond();
})->throws(\Farzai\PromptPay\Exceptions\ConfigurationException::class);

it('can write to console output', function () {
    $output = new \Symfony\Component\Console\Output\BufferedOutput;
    $promptPay = PromptPay::to('0899999999');

    $result = $promptPay->toConsole($output);

    expect($result)->toBeString();
    expect($output->fetch())->toContain('█');
});

it('can build qr code', function () {
    $qrCode = PromptPay::to('0899999999')->build();

    expect($qrCode)->toBeInstanceOf(\Farzai\PromptPay\Contracts\QrCode::class);
});

it('toFile() returns path as string', function () {
    $filename = 'test-facade-qr.png';
    $path = PromptPay::to('0899999999')->toFile($filename);

    expect($path)->toBe($filename);
    expect(file_exists($filename))->toBeTrue();

    unlink($filename);
});

it('can use withDependencies() static method', function () {
    $generator = \Farzai\PromptPay\Factories\GeneratorFactory::createDefault();
    $outputFactory = \Farzai\PromptPay\Factories\OutputFactory::create();

    $promptPay = PromptPay::withDependencies('0899999999', 100, $generator, $outputFactory);

    expect($promptPay)->toBeInstanceOf(PromptPay::class);
});

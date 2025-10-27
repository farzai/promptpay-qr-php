<?php

use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\Exceptions\UnsupportedFormatException;
use Farzai\PromptPay\Factories\OutputFactory;
use Farzai\PromptPay\QrCode;
use Psr\Http\Message\ResponseInterface;

beforeEach(function () {
    $this->qrCode = new QrCode('00020101021129370016A000000677010111011300668999999995802TH53037646304FE29');
    $this->factory = OutputFactory::create();
});

it('should throw error if not found writer', function () {
    $output = $this->factory->createDataUriOutput('jpg');
    $this->qrCode->writeTo($output);
})->throws(UnsupportedFormatException::class, 'Unsupported format: jpg, supported formats are: svg, png, pdf, gif');

it('can render qr code as psr response', function () {
    $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory;
    $output = $this->factory->createHttpResponseOutput($psr17Factory, $psr17Factory);
    $response = $this->qrCode->writeTo($output);

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($response->getStatusCode())->toBe(200);
    expect($response->getHeaderLine('Content-Type'))->toBe('image/png');

    $stringOutput = $this->factory->createStringOutput(QrFormat::PNG);
    expect($response->getBody()->getContents())->toBe($this->qrCode->writeTo($stringOutput));
});

it('can render qr code as data uri', function () {
    $dataUriOutput = $this->factory->createDataUriOutput(QrFormat::PNG);
    $dataUri = $this->qrCode->writeTo($dataUriOutput);

    $stringOutput = $this->factory->createStringOutput(QrFormat::PNG);
    expect($dataUri)->toBe('data:image/png;base64,'.base64_encode($this->qrCode->writeTo($stringOutput)));
});

it('can generate qr code and save to file', function () {
    $filename = 'qrcode.svg';

    $output = $this->factory->createFilesystemOutput($filename);
    $this->qrCode->writeTo($output);

    expect(file_exists($filename))->toBeTrue();

    unlink($filename);
});

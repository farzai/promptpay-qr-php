<?php

use Farzai\PromptPay\Outputs\DataUriOutput;
use Farzai\PromptPay\Outputs\FilesystemOutput;
use Farzai\PromptPay\Outputs\HttpResponseOutput;
use Farzai\PromptPay\Outputs\StringOutput;
use Farzai\PromptPay\QrCode;
use Psr\Http\Message\ResponseInterface;

beforeEach(function () {
    $this->qrCode = new QrCode('00020101021129370016A000000677010111011300668999999995802TH53037646304FE29');
});

it('can render qr code as psr response', function () {
    $response = $this->qrCode->writeTo(new HttpResponseOutput);

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($response->getStatusCode())->toBe(200);
    expect($response->getHeaderLine('Content-Type'))->toBe('image/png');
    expect($response->getBody()->getContents())->toBe($this->qrCode->writeTo(new StringOutput));
});

it('can render qr code as data uri', function () {
    $dataUri = $this->qrCode->writeTo(new DataUriOutput);

    expect($dataUri)->toBe('data:image/png;base64,'.base64_encode($this->qrCode->writeTo(new StringOutput)));
});

it('can generate qr code and save to file', function () {
    $filename = 'qrcode.svg';

    $this->qrCode->writeTo(new FilesystemOutput($filename));

    expect(file_exists($filename))->toBeTrue();

    unlink($filename);
});

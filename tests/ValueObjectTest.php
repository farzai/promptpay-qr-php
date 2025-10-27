<?php

use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\Services\QrCodeBuilder;
use Farzai\PromptPay\ValueObjects\Amount;
use Farzai\PromptPay\ValueObjects\OutputResult;
use Farzai\PromptPay\ValueObjects\Recipient;

describe('OutputResult Value Object', function () {
    it('creates result from data uri', function () {
        $dataUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA';
        $result = OutputResult::fromDataUri($dataUri, QrFormat::PNG);

        expect($result->getData())->toBe($dataUri);
        expect($result->getFormat())->toBe(QrFormat::PNG);
        expect($result->isDataUri())->toBeTrue();
        expect($result->isFile())->toBeFalse();
        expect($result->getTimestamp())->toBeInt();
    });

    it('creates result from file path', function () {
        $path = '/tmp/qr.png';
        $data = 'file content';
        $result = OutputResult::fromFile($path, $data);

        expect($result->getData())->toBe($data);
        expect($result->getPath())->toBe($path);
        expect($result->isFile())->toBeTrue();
        expect($result->isDataUri())->toBeFalse();
        expect($result->getTimestamp())->toBeInt();
    });

    it('creates result from string data', function () {
        $data = 'qr code binary data';
        $result = OutputResult::fromString($data, QrFormat::SVG);

        expect($result->getData())->toBe($data);
        expect($result->getFormat())->toBe(QrFormat::SVG);
        expect($result->isDataUri())->toBeFalse();
        expect($result->isFile())->toBeFalse();
    });

    it('creates result from PSR response', function () {
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory;
        $response = $psr17Factory->createResponse(200);
        $stream = $psr17Factory->createStream('response body content');
        $response = $response->withBody($stream);

        $result = OutputResult::fromResponse($response);

        expect($result->getData())->toBe('response body content');
        expect($result->getFormat())->toBe(QrFormat::PNG);
    });

    it('can get data', function () {
        $data = 'test data';
        $result = OutputResult::fromString($data, QrFormat::PNG);

        expect($result->getData())->toBe($data);
    });

    it('can get format', function () {
        $result = OutputResult::fromString('data', QrFormat::PDF);

        expect($result->getFormat())->toBe(QrFormat::PDF);
    });

    it('returns null format when not set', function () {
        $result = OutputResult::fromFile('/tmp/test.txt', 'data');

        expect($result->getFormat())->toBeNull();
    });

    it('can get path', function () {
        $path = '/tmp/qr.svg';
        $result = OutputResult::fromFile($path, 'data');

        expect($result->getPath())->toBe($path);
    });

    it('returns null path when not a file', function () {
        $result = OutputResult::fromString('data', QrFormat::PNG);

        expect($result->getPath())->toBeNull();
    });

    it('can get timestamp', function () {
        $beforeTimestamp = time();
        $result = OutputResult::fromString('data', QrFormat::PNG);
        $afterTimestamp = time();

        expect($result->getTimestamp())->toBeGreaterThanOrEqual($beforeTimestamp);
        expect($result->getTimestamp())->toBeLessThanOrEqual($afterTimestamp);
    });

    it('detects data URI correctly', function () {
        $dataUri = 'data:image/png;base64,abc123';
        $result = OutputResult::fromDataUri($dataUri, QrFormat::PNG);

        expect($result->isDataUri())->toBeTrue();
    });

    it('detects non-data URI correctly', function () {
        $result = OutputResult::fromString('regular data', QrFormat::PNG);

        expect($result->isDataUri())->toBeFalse();
    });

    it('detects file result correctly', function () {
        $result = OutputResult::fromFile('/tmp/qr.png', 'data');

        expect($result->isFile())->toBeTrue();
    });

    it('detects non-file result correctly', function () {
        $result = OutputResult::fromString('data', QrFormat::PNG);

        expect($result->isFile())->toBeFalse();
    });

    it('calculates data size correctly', function () {
        $data = 'Hello World';
        $result = OutputResult::fromString($data, QrFormat::PNG);

        expect($result->getSize())->toBe(strlen($data));
        expect($result->getSize())->toBe(11);
    });

    it('converts to string', function () {
        $data = 'test data content';
        $result = OutputResult::fromString($data, QrFormat::PNG);

        expect((string) $result)->toBe($data);
        expect($result->__toString())->toBe($data);
    });

    it('handles empty data', function () {
        $result = OutputResult::fromString('', QrFormat::PNG);

        expect($result->getData())->toBe('');
        expect($result->getSize())->toBe(0);
    });

    it('handles large data', function () {
        $largeData = str_repeat('x', 10000);
        $result = OutputResult::fromString($largeData, QrFormat::PNG);

        expect($result->getSize())->toBe(10000);
        expect($result->getData())->toBe($largeData);
    });
});

describe('Amount Value Object Extended', function () {
    it('can get raw value', function () {
        $amount = Amount::from(123.45);

        expect($amount->getValue())->toBe(123.45);
    });

    it('formats display value correctly', function () {
        $amount = Amount::from(1234567.89);

        expect($amount->getDisplayValue())->toBe('1,234,567.89');
    });

    it('formats small amounts correctly', function () {
        $amount = Amount::from(0.01);

        expect($amount->getDisplayValue())->toBe('0.01');
    });

    it('formats amounts without decimals correctly', function () {
        $amount = Amount::from(100);

        expect($amount->getDisplayValue())->toBe('100.00');
    });

    it('handles integer amounts', function () {
        $amount = Amount::from(500);

        expect($amount->getValue())->toBe(500);
        expect($amount->getDisplayValue())->toBe('500.00');
    });

    it('handles float amounts', function () {
        $amount = Amount::from(99.99);

        expect($amount->getValue())->toBe(99.99);
        expect($amount->getDisplayValue())->toBe('99.99');
    });

    it('is immutable', function () {
        $amount1 = Amount::from(100);
        $amount2 = Amount::from(200);

        expect($amount1)->not->toBe($amount2);
        expect($amount1->getValue())->toBe(100);
        expect($amount2->getValue())->toBe(200);
    });

    it('fromNumeric returns null for null value', function () {
        $amount = Amount::fromNumeric(null);

        expect($amount)->toBeNull();
    });

    it('fromNumeric creates amount from numeric value', function () {
        $amount = Amount::fromNumeric(100.50);

        expect($amount)->toBeInstanceOf(Amount::class);
        expect($amount->getValue())->toBe(100.50);
    });

    it('fromNumeric creates amount from integer', function () {
        $amount = Amount::fromNumeric(50);

        expect($amount)->toBeInstanceOf(Amount::class);
        expect($amount->getValue())->toBe(50);
    });

    it('isZero returns true for zero amount', function () {
        $amount = Amount::from(0);

        expect($amount->isZero())->toBeTrue();
    });

    it('isZero returns true for zero float amount', function () {
        $amount = Amount::from(0.0);

        expect($amount->isZero())->toBeTrue();
    });

    it('isZero returns false for non-zero amount', function () {
        $amount = Amount::from(100);

        expect($amount->isZero())->toBeFalse();
    });

    it('isPositive returns true for positive amount', function () {
        $amount = Amount::from(100);

        expect($amount->isPositive())->toBeTrue();
    });

    it('isPositive returns false for zero amount', function () {
        $amount = Amount::from(0);

        expect($amount->isPositive())->toBeFalse();
    });

    it('__toString returns formatted value', function () {
        $amount = Amount::from(123.45);

        expect((string) $amount)->toBe('123.45');
        expect($amount->__toString())->toBe('123.45');
    });

    it('__toString formats integer amounts with decimals', function () {
        $amount = Amount::from(100);

        expect((string) $amount)->toBe('100.00');
    });
});

describe('QrCodeResult Value Object', function () {
    it('can get vendor result', function () {
        $builder = QrCodeBuilder::create();
        $result = $builder->build('test-payload', QrFormat::PNG);

        $vendorResult = $result->getVendorResult();

        expect($vendorResult)->toBeInstanceOf(\Endroid\QrCode\Writer\Result\ResultInterface::class);
    });

    it('can get string from qr code', function () {
        $builder = QrCodeBuilder::create();
        $result = $builder->build('test-payload', QrFormat::PNG);

        expect($result->getString())->not->toBeEmpty();
    });

    it('can get data uri', function () {
        $builder = QrCodeBuilder::create();
        $result = $builder->build('test-payload', QrFormat::PNG);

        $dataUri = $result->getDataUri();

        expect($dataUri)->toStartWith('data:image/png');
    });

    it('can get mime type', function () {
        $builder = QrCodeBuilder::create();
        $result = $builder->build('test-payload', QrFormat::PNG);

        expect($result->getMimeType())->toBe('image/png');
    });
});

describe('Recipient Value Object', function () {
    it('__toString returns normalized value', function () {
        $recipient = Recipient::fromString('089-999-9999');

        expect((string) $recipient)->toBe('0899999999');
        expect($recipient->__toString())->toBe('0899999999');
    });

    it('__toString works for tax id', function () {
        $recipient = Recipient::fromString('1234567890123');

        expect((string) $recipient)->toBe('1234567890123');
    });

    it('__toString works for e-wallet', function () {
        $recipient = Recipient::fromString('123456789012345');

        expect((string) $recipient)->toBe('123456789012345');
    });
});

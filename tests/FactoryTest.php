<?php

use Farzai\PromptPay\Contracts\Generator;
use Farzai\PromptPay\CRC16CCITTAlgorithm;
use Farzai\PromptPay\Factories\GeneratorFactory;
use Farzai\PromptPay\Factories\OutputFactory;
use Farzai\PromptPay\Factories\PromptPayFactory;
use Farzai\PromptPay\Generator as ConcreteGenerator;
use Farzai\PromptPay\PayloadGenerator as ConcretePayloadGenerator;
use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;

describe('GeneratorFactory', function () {
    it('creates generator with default dependencies', function () {
        $generator = GeneratorFactory::createDefault();

        expect($generator)->toBeInstanceOf(Generator::class);
        expect($generator)->toBeInstanceOf(ConcreteGenerator::class);
    });

    it('creates generator with custom payload generator', function () {
        $crcAlgorithm = new CRC16CCITTAlgorithm;
        $payloadGenerator = new ConcretePayloadGenerator($crcAlgorithm);

        $generator = GeneratorFactory::createWithPayloadGenerator($payloadGenerator);

        expect($generator)->toBeInstanceOf(Generator::class);
    });

    it('creates generator with custom CRC algorithm', function () {
        $crcAlgorithm = new CRC16CCITTAlgorithm;

        $generator = GeneratorFactory::createWithCrcAlgorithm($crcAlgorithm);

        expect($generator)->toBeInstanceOf(Generator::class);
    });

    it('creates generator with all custom dependencies', function () {
        $crcAlgorithm = new CRC16CCITTAlgorithm;
        $payloadGenerator = new ConcretePayloadGenerator($crcAlgorithm);

        $generator = GeneratorFactory::create($payloadGenerator);

        expect($generator)->toBeInstanceOf(Generator::class);
    });

    it('can generate qr code using created generator', function () {
        $generator = GeneratorFactory::createDefault();
        $qrCode = $generator->generate('0899999999', 100);

        expect((string) $qrCode)->toBeString();
        expect((string) $qrCode)->toStartWith('0002010102');
    });
});

describe('PromptPayFactory', function () {
    it('creates factory with default dependencies', function () {
        $factory = PromptPayFactory::createDefault();

        expect($factory)->toBeInstanceOf(PromptPayFactory::class);
        expect($factory->getGenerator())->toBeInstanceOf(Generator::class);
        expect($factory->getOutputFactory())->toBeInstanceOf(OutputFactory::class);
    });

    it('creates factory with custom config', function () {
        $config = QrCodeConfig::create(size: 400);
        $factory = PromptPayFactory::createWithConfig($config);

        expect($factory)->toBeInstanceOf(PromptPayFactory::class);
        expect($factory->getGenerator())->toBeInstanceOf(Generator::class);
    });

    it('creates factory with null config', function () {
        $factory = PromptPayFactory::createDefault(null);

        expect($factory)->toBeInstanceOf(PromptPayFactory::class);
    });

    it('creates PromptPay instance with recipient', function () {
        $factory = PromptPayFactory::createDefault();
        $promptPay = $factory->create('0899999999');

        expect($promptPay)->toBeInstanceOf(PromptPay::class);
    });

    it('creates PromptPay instance with recipient and amount', function () {
        $factory = PromptPayFactory::createDefault();
        $promptPay = $factory->create('0899999999', 100.50);

        expect($promptPay)->toBeInstanceOf(PromptPay::class);
    });

    it('creates PromptPay instance using fluent to() method', function () {
        $factory = PromptPayFactory::createDefault();
        $promptPay = $factory->to('0899999999');

        expect($promptPay)->toBeInstanceOf(PromptPay::class);
    });

    it('can get generator instance', function () {
        $factory = PromptPayFactory::createDefault();
        $generator = $factory->getGenerator();

        expect($generator)->toBeInstanceOf(Generator::class);
    });

    it('can get output factory instance', function () {
        $factory = PromptPayFactory::createDefault();
        $outputFactory = $factory->getOutputFactory();

        expect($outputFactory)->toBeInstanceOf(OutputFactory::class);
    });

    it('creates working PromptPay instance that can generate QR code', function () {
        $factory = PromptPayFactory::createDefault();
        $promptPay = $factory->create('0899999999', 50);

        $qrCode = $promptPay->build();

        expect((string) $qrCode)->toBeString();
        expect((string) $qrCode)->toStartWith('0002010102');
    });

    it('factory maintains immutability with custom config', function () {
        $config1 = QrCodeConfig::create(size: 300);
        $config2 = QrCodeConfig::create(size: 500);

        $factory1 = PromptPayFactory::createWithConfig($config1);
        $factory2 = PromptPayFactory::createWithConfig($config2);

        expect($factory1)->not->toBe($factory2);
    });
});

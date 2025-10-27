<?php

use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\Services\QrCodeBuilder;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Farzai\PromptPay\ValueObjects\QrCodeResult;

describe('QrCodeBuilder', function () {
    it('creates builder with default config', function () {
        $builder = QrCodeBuilder::create();

        expect($builder)->toBeInstanceOf(QrCodeBuilder::class);
        expect($builder->getConfig())->toBeInstanceOf(QrCodeConfig::class);
    });

    it('creates builder with custom config', function () {
        $config = QrCodeConfig::create(size: 400);
        $builder = QrCodeBuilder::create($config);

        expect($builder->getConfig())->toBe($config);
        expect($builder->getConfig()->getSize())->toBe(400);
    });

    it('builds qr code with default config', function () {
        $builder = QrCodeBuilder::create();
        $result = $builder->build('test-payload', QrFormat::PNG);

        expect($result)->toBeInstanceOf(QrCodeResult::class);
        expect($result->getString())->not->toBeEmpty();
    });

    it('builds qr code with custom config via buildWithConfig', function () {
        $builder = QrCodeBuilder::create();
        $customConfig = QrCodeConfig::create(size: 500, margin: 20);

        $result = $builder->buildWithConfig('test-payload', QrFormat::PNG, $customConfig);

        expect($result)->toBeInstanceOf(QrCodeResult::class);
        expect($result->getString())->not->toBeEmpty();
    });

    it('gets current configuration', function () {
        $config = QrCodeConfig::create(size: 350);
        $builder = new QrCodeBuilder($config);

        $retrievedConfig = $builder->getConfig();

        expect($retrievedConfig)->toBe($config);
        expect($retrievedConfig->getSize())->toBe(350);
    });

    it('creates new builder with different config via withConfig', function () {
        $originalConfig = QrCodeConfig::create(size: 300);
        $builder1 = new QrCodeBuilder($originalConfig);

        $newConfig = QrCodeConfig::create(size: 500);
        $builder2 = $builder1->withConfig($newConfig);

        expect($builder2)->not->toBe($builder1);
        expect($builder1->getConfig())->toBe($originalConfig);
        expect($builder2->getConfig())->toBe($newConfig);
        expect($builder2->getConfig()->getSize())->toBe(500);
    });

    it('withConfig maintains immutability', function () {
        $builder1 = QrCodeBuilder::create(QrCodeConfig::create(size: 300));
        $builder2 = $builder1->withConfig(QrCodeConfig::create(size: 400));

        expect($builder1->getConfig()->getSize())->toBe(300);
        expect($builder2->getConfig()->getSize())->toBe(400);
    });

    it('builds qr code with different formats', function () {
        $builder = QrCodeBuilder::create();

        // Skip PDF format as it requires FPDF library
        $formats = [QrFormat::PNG, QrFormat::SVG, QrFormat::GIF];

        foreach ($formats as $format) {
            $result = $builder->build('test-payload', $format);
            expect($result)->toBeInstanceOf(QrCodeResult::class);
        }
    });

    it('builds qr code with custom encoding', function () {
        $config = QrCodeConfig::create(encoding: 'ISO-8859-1');
        $builder = QrCodeBuilder::create($config);

        $result = $builder->build('test-payload', QrFormat::PNG);

        expect($result)->toBeInstanceOf(QrCodeResult::class);
    });

    it('builds qr code with custom margin', function () {
        $config = QrCodeConfig::create(margin: 15);
        $builder = QrCodeBuilder::create($config);

        $result = $builder->build('test-payload', QrFormat::PNG);

        expect($result)->toBeInstanceOf(QrCodeResult::class);
    });
});

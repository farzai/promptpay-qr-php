<?php

use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\Exceptions\ConfigurationException;
use Farzai\PromptPay\Factories\OutputFactory;
use Farzai\PromptPay\Services\QrCodeBuilder;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Symfony\Component\Console\Output\BufferedOutput;

describe('OutputFactory', function () {
    it('creates factory with default QR code builder', function () {
        $factory = OutputFactory::create();

        expect($factory)->toBeInstanceOf(OutputFactory::class);
        expect($factory->getQrCodeBuilder())->toBeInstanceOf(\Farzai\PromptPay\Contracts\QrCodeBuilder::class);
    });

    it('creates factory with custom config', function () {
        $config = QrCodeConfig::create(size: 400);
        $factory = OutputFactory::create($config);

        expect($factory)->toBeInstanceOf(OutputFactory::class);
    });

    it('creates factory with custom QR code builder', function () {
        $builder = QrCodeBuilder::create();
        $factory = OutputFactory::createWithBuilder($builder);

        expect($factory)->toBeInstanceOf(OutputFactory::class);
        expect($factory->getQrCodeBuilder())->toBe($builder);
    });

    it('creates data URI output with string format', function () {
        $factory = OutputFactory::create();
        $output = $factory->createDataUriOutput('png');

        expect($output)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
    });

    it('creates data URI output with QrFormat enum', function () {
        $factory = OutputFactory::create();
        $output = $factory->createDataUriOutput(QrFormat::PNG);

        expect($output)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
    });

    it('creates filesystem output', function () {
        $factory = OutputFactory::create();
        $output = $factory->createFilesystemOutput('/tmp/qr.png');

        expect($output)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
    });

    it('creates HTTP response output with PSR-17 factories', function () {
        $factory = OutputFactory::create();
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory;

        $output = $factory->createHttpResponseOutput($psr17Factory, $psr17Factory);

        expect($output)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
    });

    it('throws exception when creating HTTP response without factories', function () {
        $factory = OutputFactory::create();

        $factory->createHttpResponseOutput(null, null);
    })->throws(ConfigurationException::class);

    it('throws exception when creating HTTP response without response factory', function () {
        $factory = OutputFactory::create();
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory;

        $factory->createHttpResponseOutput(null, $psr17Factory);
    })->throws(ConfigurationException::class);

    it('throws exception when creating HTTP response without stream factory', function () {
        $factory = OutputFactory::create();
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory;

        $factory->createHttpResponseOutput($psr17Factory, null);
    })->throws(ConfigurationException::class);

    it('creates string output with string format', function () {
        $factory = OutputFactory::create();
        $output = $factory->createStringOutput('svg');

        expect($output)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
    });

    it('creates string output with QrFormat enum', function () {
        $factory = OutputFactory::create();
        $output = $factory->createStringOutput(QrFormat::SVG);

        expect($output)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
    });

    it('creates console output', function () {
        $factory = OutputFactory::create();
        $consoleOutput = new BufferedOutput;

        $output = $factory->createConsoleOutput($consoleOutput);

        expect($output)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
    });

    it('can get QR code builder', function () {
        $factory = OutputFactory::create();
        $builder = $factory->getQrCodeBuilder();

        expect($builder)->toBeInstanceOf(\Farzai\PromptPay\Contracts\QrCodeBuilder::class);
    });

    it('creates different output types with same factory', function () {
        $factory = OutputFactory::create();

        $dataUriOutput = $factory->createDataUriOutput('png');
        $stringOutput = $factory->createStringOutput('svg');
        $fileOutput = $factory->createFilesystemOutput('/tmp/qr.png');

        expect($dataUriOutput)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
        expect($stringOutput)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
        expect($fileOutput)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
    });

    it('handles all supported formats for data URI output', function () {
        $factory = OutputFactory::create();

        foreach (['png', 'svg', 'pdf', 'gif'] as $format) {
            $output = $factory->createDataUriOutput($format);
            expect($output)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
        }
    });

    it('handles all supported formats for string output', function () {
        $factory = OutputFactory::create();

        foreach (['png', 'svg', 'pdf', 'gif'] as $format) {
            $output = $factory->createStringOutput($format);
            expect($output)->toBeInstanceOf(\Farzai\PromptPay\Contracts\OutputInterface::class);
        }
    });

    it('filesystem output throws exception for path without extension', function () {
        $factory = OutputFactory::create();
        $output = $factory->createFilesystemOutput('/tmp/qrcode');

        expect(fn () => $output->write('test-payload'))
            ->toThrow(\Farzai\PromptPay\Exceptions\ConfigurationException::class, 'no file extension');
    });
});

<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Factories;

use Farzai\PromptPay\Contracts\OutputInterface;
use Farzai\PromptPay\Contracts\QrCodeBuilder;
use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\Exceptions\ConfigurationException;
use Farzai\PromptPay\Outputs\DataUriOutput;
use Farzai\PromptPay\Outputs\FilesystemOutput;
use Farzai\PromptPay\Outputs\HttpResponseOutput;
use Farzai\PromptPay\Outputs\StringOutput;
use Farzai\PromptPay\Services\QrCodeBuilder as ConcreteQrCodeBuilder;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Console\Output\OutputInterface as SymfonyOutputInterface;

/**
 * Factory for creating output instances
 * Implements Factory Pattern for better extensibility
 */
final class OutputFactory
{
    public function __construct(
        private readonly QrCodeBuilder $qrCodeBuilder
    ) {}

    /**
     * Create factory with default QR code builder
     */
    public static function create(?QrCodeConfig $config = null): self
    {
        $qrCodeBuilder = ConcreteQrCodeBuilder::create($config);

        return new self($qrCodeBuilder);
    }

    /**
     * Create factory with custom QR code builder
     */
    public static function createWithBuilder(QrCodeBuilder $qrCodeBuilder): self
    {
        return new self($qrCodeBuilder);
    }

    /**
     * Create Data URI output
     */
    public function createDataUriOutput(string|QrFormat $format): OutputInterface
    {
        $format = $this->ensureFormat($format);

        return new DataUriOutput($this->qrCodeBuilder, $format);
    }

    /**
     * Create Filesystem output
     */
    public function createFilesystemOutput(string $path): OutputInterface
    {
        return new FilesystemOutput($this->qrCodeBuilder, $path);
    }

    /**
     * Create HTTP Response output
     *
     * @param  ResponseFactoryInterface|null  $responseFactory  PSR-17 response factory (required)
     * @param  StreamFactoryInterface|null  $streamFactory  PSR-17 stream factory (required)
     *
     * @throws ConfigurationException If PSR-17 factories are not provided
     */
    public function createHttpResponseOutput(
        ?ResponseFactoryInterface $responseFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ): OutputInterface {
        if ($responseFactory === null || $streamFactory === null) {
            throw new ConfigurationException(
                'HttpResponseOutput requires PSR-17 factories. '.
                'Please install a PSR-17 implementation (e.g., composer require nyholm/psr7 or guzzlehttp/psr7) '.
                'and pass the factories to this method. '.
                'Example: $factory = new \Nyholm\Psr7\Factory\Psr17Factory(); '.
                '$output = $outputFactory->createHttpResponseOutput($factory, $factory);'
            );
        }

        return new HttpResponseOutput($this->qrCodeBuilder, $responseFactory, $streamFactory);
    }

    /**
     * Create String output
     */
    public function createStringOutput(string|QrFormat $format): OutputInterface
    {
        $format = $this->ensureFormat($format);

        return new StringOutput($this->qrCodeBuilder, $format);
    }

    /**
     * Create Console output
     */
    public function createConsoleOutput(SymfonyOutputInterface $output): OutputInterface
    {
        return new \Farzai\PromptPay\Outputs\ConsoleOutput($this->qrCodeBuilder, $output);
    }

    /**
     * Ensure format is QrFormat enum
     */
    private function ensureFormat(string|QrFormat $format): QrFormat
    {
        if (is_string($format)) {
            return QrFormat::fromString($format);
        }

        return $format;
    }

    /**
     * Get QR code builder
     */
    public function getQrCodeBuilder(): QrCodeBuilder
    {
        return $this->qrCodeBuilder;
    }
}

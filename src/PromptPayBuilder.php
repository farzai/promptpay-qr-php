<?php

declare(strict_types=1);

namespace Farzai\PromptPay;

use Farzai\PromptPay\Contracts\Generator;
use Farzai\PromptPay\Contracts\QrCode;
use Farzai\PromptPay\Factories\OutputFactory;
use Farzai\PromptPay\ValueObjects\OutputResult;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Immutable builder for creating PromptPay QR codes
 * Provides fluent interface for configuration
 */
final class PromptPayBuilder
{
    public function __construct(
        private readonly string $recipient,
        private readonly int|float|null $amount,
        private readonly Generator $generator,
        private readonly OutputFactory $outputFactory
    ) {}

    /**
     * Set amount (returns new instance - immutable)
     */
    public function withAmount(int|float|null $amount): self
    {
        return new self(
            $this->recipient,
            $amount,
            $this->generator,
            $this->outputFactory
        );
    }

    /**
     * Set configuration (returns new instance - immutable)
     */
    public function withConfig(QrCodeConfig $config): self
    {
        $outputFactory = OutputFactory::create($config);

        return new self(
            $this->recipient,
            $this->amount,
            $this->generator,
            $outputFactory
        );
    }

    /**
     * Build qr code
     */
    public function build(): QrCode
    {
        return $this->generator->generate(
            recipient: $this->recipient,
            amount: $this->amount,
        );
    }

    /**
     * Get qr code as data uri
     */
    public function toDataUri(string $format): OutputResult
    {
        $output = $this->outputFactory->createDataUriOutput($format);
        $dataUri = $this->build()->writeTo($output);

        return OutputResult::fromDataUri($dataUri, \Farzai\PromptPay\Enums\QrFormat::fromString($format));
    }

    /**
     * Save qr code to filesystem
     */
    public function toFile(string $path): OutputResult
    {
        $output = $this->outputFactory->createFilesystemOutput($path);
        $result = $this->build()->writeTo($output);

        return OutputResult::fromFile($path, $result);
    }

    /**
     * Response qr code as http response
     *
     * Requires PSR-17 factories. Install a PSR-17 implementation like:
     * - composer require nyholm/psr7
     * - composer require guzzlehttp/psr7
     *
     * @param ResponseFactoryInterface|null $responseFactory PSR-17 response factory (required)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (required)
     */
    public function toResponse(
        ?ResponseFactoryInterface $responseFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ): ResponseInterface {
        $output = $this->outputFactory->createHttpResponseOutput($responseFactory, $streamFactory);

        return $this->build()->writeTo($output);
    }

    /**
     * Write qr code to console
     */
    public function toConsole(OutputInterface $output): string
    {
        $consoleOutput = $this->outputFactory->createConsoleOutput($output);

        return $this->build()->writeTo($consoleOutput);
    }

    /**
     * Get the QR code payload as string
     */
    public function toPayload(): string
    {
        return (string) $this->build();
    }

    /**
     * Get recipient
     */
    public function getRecipient(): string
    {
        return $this->recipient;
    }

    /**
     * Get amount
     */
    public function getAmount(): int|float|null
    {
        return $this->amount;
    }
}

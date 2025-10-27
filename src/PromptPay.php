<?php

declare(strict_types=1);

namespace Farzai\PromptPay;

use Farzai\PromptPay\Contracts\Generator;
use Farzai\PromptPay\Contracts\QrCode;
use Farzai\PromptPay\Factories\GeneratorFactory;
use Farzai\PromptPay\Factories\OutputFactory;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PromptPay QR Code Generator - Facade
 * Provides convenient static methods for common use cases
 * For advanced usage with immutable builder pattern, use PromptPayBuilder
 */
class PromptPay
{
    private readonly PromptPayBuilder $builder;

    /**
     * Create qr code using default dependencies (backward compatible)
     *
     * @param  int|float|null  $amount
     */
    public static function create(string $recipient, $amount = null): QrCode
    {
        $generator = GeneratorFactory::createDefault();
        $outputFactory = OutputFactory::create();
        $builder = new PromptPayBuilder($recipient, $amount, $generator, $outputFactory);

        return $builder->build();
    }

    /**
     * Start creating qr code using default dependencies (backward compatible)
     * Returns legacy PromptPay instance for fluent API
     */
    public static function to(string $recipient): self
    {
        $generator = GeneratorFactory::createDefault();
        $outputFactory = OutputFactory::create();

        return new self($recipient, null, $generator, $outputFactory);
    }

    /**
     * Generate QR code with fluent builder pattern (new immutable API)
     * Recommended for new code
     */
    public static function generate(string $recipient): PromptPayBuilder
    {
        $generator = GeneratorFactory::createDefault();
        $outputFactory = OutputFactory::create();

        return new PromptPayBuilder($recipient, null, $generator, $outputFactory);
    }

    /**
     * Create QR code with amount using fluent builder pattern (new API)
     */
    public static function qrCode(string $recipient, int|float|null $amount = null): PromptPayBuilder
    {
        $generator = GeneratorFactory::createDefault();
        $outputFactory = OutputFactory::create();

        return new PromptPayBuilder($recipient, $amount, $generator, $outputFactory);
    }

    /**
     * Create instance with custom dependencies (for testing and customization)
     */
    public static function withDependencies(
        string $recipient,
        int|float|null $amount,
        Generator $generator,
        OutputFactory $outputFactory
    ): self {
        return new self($recipient, $amount, $generator, $outputFactory);
    }

    /**
     * Set amount (returns new instance for immutability)
     * Note: Creates new builder internally
     *
     * @param  int|float|null  $amount
     */
    public function amount($amount): self
    {
        $newBuilder = $this->builder->withAmount($amount);

        return new self(
            $newBuilder->getRecipient(),
            $newBuilder->getAmount(),
            GeneratorFactory::createDefault(),
            OutputFactory::create()
        );
    }

    /**
     * Configure QR code settings (returns new instance for immutability)
     */
    public function config(QrCodeConfig $config): self
    {
        $newBuilder = $this->builder->withConfig($config);
        $outputFactory = OutputFactory::create($config);

        return new self(
            $newBuilder->getRecipient(),
            $newBuilder->getAmount(),
            GeneratorFactory::createDefault(),
            $outputFactory
        );
    }

    /**
     * Build qr code
     */
    public function build(): QrCode
    {
        return $this->builder->build();
    }

    /**
     * Get qr code as data uri (backward compatible - returns string)
     */
    public function toDataUri(string $format): string
    {
        $result = $this->builder->toDataUri($format);

        return $result->getData();
    }

    /**
     * Save qr code to filesystem (backward compatible - returns path)
     */
    public function toFile(string $path): string
    {
        $result = $this->builder->toFile($path);

        return $result->getPath() ?? $path;
    }

    /**
     * Response qr code as http response
     */
    public function respond(): ResponseInterface
    {
        return $this->builder->toResponse();
    }

    /**
     * Write qr code to console
     */
    public function toConsole(OutputInterface $output): string
    {
        return $this->builder->toConsole($output);
    }

    private function __construct(
        string $recipient,
        int|float|null $amount,
        Generator $generator,
        OutputFactory $outputFactory
    ) {
        $this->builder = new PromptPayBuilder($recipient, $amount, $generator, $outputFactory);
    }
}

<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Factories;

use Farzai\PromptPay\Contracts\Generator;
use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;

/**
 * Factory for creating PromptPay instances with pre-wired dependencies
 * Eliminates tight coupling and enables dependency injection
 */
final class PromptPayFactory
{
    public function __construct(
        private readonly Generator $generator,
        private readonly OutputFactory $outputFactory
    ) {}

    /**
     * Create factory with default dependencies
     */
    public static function createDefault(?QrCodeConfig $config = null): self
    {
        $generator = GeneratorFactory::createDefault();
        $outputFactory = OutputFactory::create($config);

        return new self($generator, $outputFactory);
    }

    /**
     * Create factory with custom configuration
     */
    public static function createWithConfig(QrCodeConfig $config): self
    {
        return self::createDefault($config);
    }

    /**
     * Create PromptPay instance with recipient
     */
    public function create(string $recipient, int|float|null $amount = null): PromptPay
    {
        return PromptPay::withDependencies(
            $recipient,
            $amount,
            $this->generator,
            $this->outputFactory
        );
    }

    /**
     * Create PromptPay instance using fluent interface
     */
    public function to(string $recipient): PromptPay
    {
        return $this->create($recipient);
    }

    /**
     * Get the generator instance
     */
    public function getGenerator(): Generator
    {
        return $this->generator;
    }

    /**
     * Get the output factory instance
     */
    public function getOutputFactory(): OutputFactory
    {
        return $this->outputFactory;
    }
}

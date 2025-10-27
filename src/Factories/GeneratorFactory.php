<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Factories;

use Farzai\PromptPay\Contracts\CrcAlgorithm;
use Farzai\PromptPay\Contracts\Generator;
use Farzai\PromptPay\Contracts\PayloadGenerator;
use Farzai\PromptPay\CRC16CCITTAlgorithm;
use Farzai\PromptPay\Generator as ConcreteGenerator;
use Farzai\PromptPay\PayloadGenerator as ConcretePayloadGenerator;

/**
 * Factory for creating Generator instances with dependencies
 * Provides clean dependency injection without tight coupling
 */
final class GeneratorFactory
{
    /**
     * Create generator with default dependencies
     */
    public static function createDefault(): Generator
    {
        $crcAlgorithm = new CRC16CCITTAlgorithm;
        $payloadGenerator = new ConcretePayloadGenerator($crcAlgorithm);

        return new ConcreteGenerator($payloadGenerator);
    }

    /**
     * Create generator with custom payload generator
     */
    public static function createWithPayloadGenerator(PayloadGenerator $payloadGenerator): Generator
    {
        return new ConcreteGenerator($payloadGenerator);
    }

    /**
     * Create generator with custom CRC algorithm
     */
    public static function createWithCrcAlgorithm(CrcAlgorithm $crcAlgorithm): Generator
    {
        $payloadGenerator = new ConcretePayloadGenerator($crcAlgorithm);

        return new ConcreteGenerator($payloadGenerator);
    }

    /**
     * Create generator with all custom dependencies
     */
    public static function create(
        PayloadGenerator $payloadGenerator,
    ): Generator {
        return new ConcreteGenerator($payloadGenerator);
    }
}

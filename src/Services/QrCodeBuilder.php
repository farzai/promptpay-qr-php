<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Farzai\PromptPay\Contracts\QrCodeBuilder as QrCodeBuilderContract;
use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;

/**
 * QR Code Builder Service - Centralized QR code generation
 * Eliminates code duplication across output classes
 */
final class QrCodeBuilder implements QrCodeBuilderContract
{
    public function __construct(
        private readonly QrCodeConfig $config = new QrCodeConfig
    ) {}

    public static function create(?QrCodeConfig $config = null): self
    {
        return new self($config ?? QrCodeConfig::default());
    }

    /**
     * Build QR code with specified format and payload
     */
    public function build(string $payload, QrFormat $format): ResultInterface
    {
        $builder = Builder::create()
            ->writer($format->createWriter())
            ->data($payload)
            ->encoding(new Encoding($this->config->getEncoding()))
            ->size($this->config->getSize())
            ->margin($this->config->getMargin());

        return $builder->build();
    }

    /**
     * Build QR code with custom configuration
     */
    public function buildWithConfig(
        string $payload,
        QrFormat $format,
        QrCodeConfig $customConfig
    ): ResultInterface {
        $builder = Builder::create()
            ->writer($format->createWriter())
            ->data($payload)
            ->encoding(new Encoding($customConfig->getEncoding()))
            ->size($customConfig->getSize())
            ->margin($customConfig->getMargin());

        return $builder->build();
    }

    /**
     * Get current configuration
     */
    public function getConfig(): QrCodeConfig
    {
        return $this->config;
    }

    /**
     * Create new builder with different configuration
     */
    public function withConfig(QrCodeConfig $config): self
    {
        return new self($config);
    }
}

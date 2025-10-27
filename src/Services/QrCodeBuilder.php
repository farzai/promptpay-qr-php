<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Farzai\PromptPay\Contracts\QrCodeBuilder as QrCodeBuilderContract;
use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Farzai\PromptPay\ValueObjects\QrCodeResult;

/**
 * QR Code Builder Service - Centralized QR code generation
 *
 * Adapter implementation that wraps the Endroid QR Code library.
 * This design allows for easy replacement of the underlying QR library
 * without affecting the rest of the application.
 *
 * @see QrCodeResult For the vendor-agnostic result wrapper
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
    public function build(string $payload, QrFormat $format): QrCodeResult
    {
        $vendorResult = $this->buildVendorResult($payload, $format, $this->config);

        return QrCodeResult::fromVendorResult($vendorResult);
    }

    /**
     * Build QR code with custom configuration
     */
    public function buildWithConfig(
        string $payload,
        QrFormat $format,
        QrCodeConfig $customConfig
    ): QrCodeResult {
        $vendorResult = $this->buildVendorResult($payload, $format, $customConfig);

        return QrCodeResult::fromVendorResult($vendorResult);
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

    /**
     * Build QR code using the vendor library
     *
     * @internal This method encapsulates the vendor-specific logic
     */
    private function buildVendorResult(
        string $payload,
        QrFormat $format,
        QrCodeConfig $config
    ): \Endroid\QrCode\Writer\Result\ResultInterface {
        $builder = Builder::create()
            ->writer($format->createWriter())
            ->data($payload)
            ->encoding(new Encoding($config->getEncoding()))
            ->size($config->getSize())
            ->margin($config->getMargin());

        return $builder->build();
    }
}

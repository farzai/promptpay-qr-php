<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Contracts;

use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Farzai\PromptPay\ValueObjects\QrCodeResult;

/**
 * QR Code Builder Contract
 *
 * Defines the interface for QR code generation.
 * This contract is vendor-agnostic and can be implemented with any QR code library.
 */
interface QrCodeBuilder
{
    /**
     * Build QR code with specified format and payload
     */
    public function build(string $payload, QrFormat $format): QrCodeResult;

    /**
     * Build QR code with custom configuration
     */
    public function buildWithConfig(
        string $payload,
        QrFormat $format,
        QrCodeConfig $customConfig
    ): QrCodeResult;

    /**
     * Get current configuration
     */
    public function getConfig(): QrCodeConfig;

    /**
     * Create new builder with different configuration
     */
    public function withConfig(QrCodeConfig $config): self;
}

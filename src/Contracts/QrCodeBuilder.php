<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Contracts;

use Endroid\QrCode\Writer\Result\ResultInterface;
use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;

interface QrCodeBuilder
{
    /**
     * Build QR code with specified format and payload
     */
    public function build(string $payload, QrFormat $format): ResultInterface;

    /**
     * Build QR code with custom configuration
     */
    public function buildWithConfig(
        string $payload,
        QrFormat $format,
        QrCodeConfig $customConfig
    ): ResultInterface;

    /**
     * Get current configuration
     */
    public function getConfig(): QrCodeConfig;

    /**
     * Create new builder with different configuration
     */
    public function withConfig(QrCodeConfig $config): self;
}

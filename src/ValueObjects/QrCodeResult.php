<?php

declare(strict_types=1);

namespace Farzai\PromptPay\ValueObjects;

use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * QR Code Result Adapter
 *
 * Wraps the vendor QR code result to isolate external dependencies.
 * This adapter pattern allows easy swapping of QR code libraries in the future.
 *
 * @psalm-immutable
 */
final class QrCodeResult
{
    public function __construct(
        private readonly ResultInterface $vendorResult
    ) {}

    /**
     * Create from vendor result
     */
    public static function fromVendorResult(ResultInterface $vendorResult): self
    {
        return new self($vendorResult);
    }

    /**
     * Get QR code as string
     */
    public function getString(): string
    {
        return $this->vendorResult->getString();
    }

    /**
     * Save QR code to file
     */
    public function saveToFile(string $path): void
    {
        $this->vendorResult->saveToFile($path);
    }

    /**
     * Get QR code as data URI
     */
    public function getDataUri(): string
    {
        return $this->vendorResult->getDataUri();
    }

    /**
     * Get MIME type
     */
    public function getMimeType(): string
    {
        return $this->vendorResult->getMimeType();
    }

    /**
     * Get the underlying vendor result (for advanced use cases)
     *
     * @internal Use with caution - breaks abstraction
     */
    public function getVendorResult(): ResultInterface
    {
        return $this->vendorResult;
    }
}

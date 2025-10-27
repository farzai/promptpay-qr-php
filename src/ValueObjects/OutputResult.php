<?php

declare(strict_types=1);

namespace Farzai\PromptPay\ValueObjects;

use Farzai\PromptPay\Enums\QrFormat;

/**
 * Output result value object
 * Provides consistent return type for all output operations with metadata
 */
final class OutputResult
{
    public function __construct(
        private readonly string $data,
        private readonly ?QrFormat $format = null,
        private readonly ?string $path = null,
        private readonly ?int $timestamp = null
    ) {}

    /**
     * Create result from data uri
     */
    public static function fromDataUri(string $dataUri, QrFormat $format): self
    {
        return new self(
            data: $dataUri,
            format: $format,
            timestamp: time()
        );
    }

    /**
     * Create result from file path
     */
    public static function fromFile(string $path, string $data): self
    {
        return new self(
            data: $data,
            path: $path,
            timestamp: time()
        );
    }

    /**
     * Create result from string data
     */
    public static function fromString(string $data, QrFormat $format): self
    {
        return new self(
            data: $data,
            format: $format,
            timestamp: time()
        );
    }

    /**
     * Create result from response
     */
    public static function fromResponse(mixed $response): self
    {
        return new self(
            data: (string) $response->getBody(),
            format: QrFormat::PNG,
            timestamp: time()
        );
    }

    /**
     * Get the output data
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Get the format
     */
    public function getFormat(): ?QrFormat
    {
        return $this->format;
    }

    /**
     * Get the file path (if saved to file)
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Get the timestamp
     */
    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    /**
     * Check if this is a file output
     */
    public function isFile(): bool
    {
        return $this->path !== null;
    }

    /**
     * Check if this is a data URI
     */
    public function isDataUri(): bool
    {
        return str_starts_with($this->data, 'data:');
    }

    /**
     * Get data size in bytes
     */
    public function getSize(): int
    {
        return strlen($this->data);
    }

    /**
     * Convert to string (returns the data)
     */
    public function __toString(): string
    {
        return $this->data;
    }
}

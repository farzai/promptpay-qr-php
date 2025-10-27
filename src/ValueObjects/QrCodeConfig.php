<?php

declare(strict_types=1);

namespace Farzai\PromptPay\ValueObjects;

/**
 * QR Code configuration value object
 */
final class QrCodeConfig
{
    public function __construct(
        private readonly int $size = 300,
        private readonly int $margin = 10,
        private readonly string $encoding = 'UTF-8'
    ) {}

    public static function default(): self
    {
        return new self;
    }

    public static function create(int $size = 300, int $margin = 10, string $encoding = 'UTF-8'): self
    {
        return new self($size, $margin, $encoding);
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getMargin(): int
    {
        return $this->margin;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function withSize(int $size): self
    {
        return new self($size, $this->margin, $this->encoding);
    }

    public function withMargin(int $margin): self
    {
        return new self($this->size, $margin, $this->encoding);
    }

    public function withEncoding(string $encoding): self
    {
        return new self($this->size, $this->margin, $encoding);
    }
}

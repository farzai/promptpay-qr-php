<?php

declare(strict_types=1);

namespace Farzai\PromptPay\ValueObjects;

use Farzai\PromptPay\Exceptions\InvalidAmountException;
use Farzai\PromptPay\Validators\AmountValidator;

/**
 * Amount value object representing transaction amount
 */
final class Amount
{
    private function __construct(
        private readonly int|float $value
    ) {
        AmountValidator::validate($value);
    }

    public static function fromNumeric(int|float|null $value): ?self
    {
        if (is_null($value)) {
            return null;
        }

        if (! is_numeric($value)) {
            throw InvalidAmountException::notNumeric($value);
        }

        return new self($value);
    }

    /**
     * Create from numeric value (throws on invalid)
     */
    public static function from(int|float $value): self
    {
        AmountValidator::validate($value);

        return new self($value);
    }

    /**
     * Create positive amount (non-zero)
     */
    public static function positive(int|float $value): self
    {
        AmountValidator::validatePositive($value);

        return new self($value);
    }

    public function getValue(): int|float
    {
        return $this->value;
    }

    public function getFormatted(): string
    {
        return number_format($this->value, 2, '.', '');
    }

    /**
     * Get formatted value for display (with thousands separator)
     */
    public function getDisplayValue(): string
    {
        return AmountValidator::formatForDisplay($this->value);
    }

    public function isPresent(): bool
    {
        return true;
    }

    public function isZero(): bool
    {
        return $this->value === 0 || $this->value === 0.0;
    }

    public function isPositive(): bool
    {
        return $this->value > 0;
    }

    public function __toString(): string
    {
        return $this->getFormatted();
    }
}

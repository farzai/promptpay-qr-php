<?php

declare(strict_types=1);

namespace Farzai\PromptPay\ValueObjects;

use Farzai\PromptPay\Validators\RecipientValidator;

/**
 * Recipient value object representing a PromptPay recipient
 * Can be a phone number, tax ID, or e-wallet ID
 */
final class Recipient
{
    private readonly string $normalized;

    private readonly RecipientType $type;

    private function __construct(string $value)
    {
        RecipientValidator::validate($value);
        $this->normalized = RecipientValidator::normalize($value);
        $this->type = $this->determineType($this->normalized);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->normalized;
    }

    public function getType(): RecipientType
    {
        return $this->type;
    }

    public function getFormattedValue(): string
    {
        if ($this->type === RecipientType::PHONE) {
            // Convert 0899999999 to 66899999999 and pad to 13 digits
            $value = preg_replace('/^0/', '66', $this->normalized) ?? $this->normalized;

            return str_pad($value, 13, '0', STR_PAD_LEFT);
        }

        return $this->normalized;
    }

    /**
     * Get formatted value for display (with dashes/formatting)
     */
    public function getDisplayValue(): string
    {
        return RecipientValidator::formatForDisplay($this->normalized);
    }

    private function determineType(string $value): RecipientType
    {
        $length = strlen($value);

        if ($length >= 15) {
            return RecipientType::EWALLET;
        }

        if ($length >= 13) {
            return RecipientType::TAX_ID;
        }

        return RecipientType::PHONE;
    }

    public function __toString(): string
    {
        return $this->normalized;
    }
}

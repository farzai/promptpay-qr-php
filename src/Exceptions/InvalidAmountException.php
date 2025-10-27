<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Exceptions;

/**
 * Exception thrown when an invalid amount is provided
 */
class InvalidAmountException extends PromptPayException
{
    // Error codes
    public const CODE_NOT_NUMERIC = 2001;
    public const CODE_NEGATIVE = 2002;
    public const CODE_TOO_LARGE = 2003;
    public const CODE_ZERO = 2004;
    public const CODE_TOO_SMALL = 2005;

    public static function notNumeric(mixed $value): self
    {
        return new self(
            sprintf(
                'Invalid amount: "%s" is not numeric. Amount must be a number (e.g., 100, 99.50).',
                $value
            ),
            self::CODE_NOT_NUMERIC
        );
    }

    public static function negative(int|float $value): self
    {
        return new self(
            sprintf(
                'Invalid amount: %s THB cannot be negative. Please provide a positive amount.',
                number_format($value, 2)
            ),
            self::CODE_NEGATIVE
        );
    }

    public static function tooLarge(int|float $value, int|float $max): self
    {
        return new self(
            sprintf(
                "Invalid amount: %s THB exceeds maximum allowed amount of %s THB.\nThis is a PromptPay system limit.",
                number_format($value, 2),
                number_format($max, 2)
            ),
            self::CODE_TOO_LARGE
        );
    }

    public static function zero(): self
    {
        return new self(
            'Invalid amount: Amount cannot be zero. Please provide a positive amount or omit the amount for a static QR code.',
            self::CODE_ZERO
        );
    }

    public static function tooSmall(int|float $value, int|float $min): self
    {
        return new self(
            sprintf(
                'Invalid amount: %s THB is too small. Minimum amount is %s THB.',
                number_format($value, 2),
                number_format($min, 2)
            ),
            self::CODE_TOO_SMALL
        );
    }
}

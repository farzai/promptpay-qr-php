<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when recipient is invalid
 * Extends InvalidArgumentException for backward compatibility
 */
class InvalidRecipientException extends InvalidArgumentException
{
    // Error codes
    public const CODE_EMPTY = 1001;

    public const CODE_NOT_NUMERIC = 1002;

    public const CODE_INVALID_LENGTH = 1003;

    public const CODE_EMPTY_AFTER_NORMALIZATION = 1004;

    public static function empty(): self
    {
        return new self(
            'Recipient cannot be empty. Please provide a phone number (10 digits), Tax ID (13 digits), or E-Wallet ID (15 digits).',
            self::CODE_EMPTY
        );
    }

    public static function emptyAfterNormalization(string $original): self
    {
        return new self(
            sprintf(
                'Recipient "%s" contains no valid digits. Please provide a phone number, Tax ID, or E-Wallet ID.',
                $original
            ),
            self::CODE_EMPTY_AFTER_NORMALIZATION
        );
    }

    public static function notNumeric(string $value): self
    {
        return new self(
            sprintf(
                'Recipient "%s" must contain only digits. Special characters and spaces will be removed automatically.',
                $value
            ),
            self::CODE_NOT_NUMERIC
        );
    }

    public static function invalidLength(string $normalized, int $length): self
    {
        $suggestions = self::getSuggestions($length);

        return new self(
            sprintf(
                "Invalid recipient length: %d digits. Expected formats:\n%s\nProvided: %s",
                $length,
                $suggestions,
                $normalized
            ),
            self::CODE_INVALID_LENGTH
        );
    }

    private static function getSuggestions(int $length): string
    {
        $suggestions = [
            '• Phone Number: 10 digits (e.g., 0899999999)',
            '• Tax ID: 13 digits (e.g., 1234567890123)',
            '• E-Wallet ID: 15 digits (e.g., 123456789012345)',
        ];

        if ($length < 10) {
            return 'Too short! '.implode("\n", $suggestions);
        }

        if ($length === 11 || $length === 12) {
            return 'Invalid length. '.implode("\n", $suggestions);
        }

        if ($length === 14) {
            return 'Close! Add one more digit for Tax ID (13) or E-Wallet ID (15).';
        }

        if ($length > 15) {
            return 'Too long! Maximum is 15 digits for E-Wallet ID.';
        }

        return implode("\n", $suggestions);
    }
}

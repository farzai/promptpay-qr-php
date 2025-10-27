<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Validators;

use Farzai\PromptPay\Exceptions\InvalidRecipientException;

/**
 * Validator for PromptPay recipient identifiers
 * Provides detailed validation with helpful error messages
 */
final class RecipientValidator
{
    private const MIN_LENGTH = 10;

    private const MAX_LENGTH = 15;

    private const PHONE_LENGTH = 10;

    private const TAX_ID_LENGTH = 13;

    private const EWALLET_LENGTH = 15;

    /**
     * Validate recipient identifier
     *
     * @throws InvalidRecipientException
     */
    public static function validate(string $value): void
    {
        if (empty($value)) {
            throw InvalidRecipientException::empty();
        }

        $normalized = self::normalize($value);

        if (empty($normalized)) {
            throw InvalidRecipientException::emptyAfterNormalization($value);
        }

        if (! self::isNumeric($normalized)) {
            throw InvalidRecipientException::notNumeric($value);
        }

        if (! self::isValidLength($normalized)) {
            throw InvalidRecipientException::invalidLength(
                $normalized,
                strlen($normalized)
            );
        }
    }

    /**
     * Normalize recipient by removing non-digit characters
     */
    public static function normalize(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    /**
     * Check if value is numeric after normalization
     */
    private static function isNumeric(string $value): bool
    {
        return ctype_digit($value);
    }

    /**
     * Check if length is valid for any recipient type
     */
    private static function isValidLength(string $value): bool
    {
        $length = strlen($value);

        return $length >= self::MIN_LENGTH && $length <= self::MAX_LENGTH;
    }

    /**
     * Determine the type of recipient based on length
     */
    public static function determineType(string $normalized): string
    {
        $length = strlen($normalized);

        if ($length >= self::EWALLET_LENGTH) {
            return 'E-Wallet ID';
        }

        if ($length >= self::TAX_ID_LENGTH) {
            return 'Tax ID';
        }

        return 'Phone Number';
    }

    /**
     * Format phone number for display (for Thai format)
     */
    public static function formatForDisplay(string $normalized): string
    {
        $length = strlen($normalized);

        if ($length === self::PHONE_LENGTH) {
            // Format as 0XX-XXX-XXXX
            return sprintf(
                '%s-%s-%s',
                substr($normalized, 0, 3),
                substr($normalized, 3, 3),
                substr($normalized, 6)
            );
        }

        if ($length === self::TAX_ID_LENGTH) {
            // Format as X-XXXX-XXXXX-XX-X
            return sprintf(
                '%s-%s-%s-%s-%s',
                substr($normalized, 0, 1),
                substr($normalized, 1, 4),
                substr($normalized, 5, 5),
                substr($normalized, 10, 2),
                substr($normalized, 12, 1)
            );
        }

        return $normalized;
    }
}

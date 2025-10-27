<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Validators;

use Farzai\PromptPay\Exceptions\InvalidAmountException;

/**
 * Validator for transaction amounts
 * Provides detailed validation with helpful error messages
 */
final class AmountValidator
{
    private const MIN_AMOUNT = 0.01;

    private const MAX_AMOUNT = 999999999.99;

    private const RECOMMENDED_MAX = 1000000.00; // 1 million THB

    /**
     * Validate amount value
     *
     * @throws InvalidAmountException
     */
    public static function validate(int|float $value): void
    {
        if (! is_numeric($value)) {
            throw InvalidAmountException::notNumeric($value);
        }

        if ($value < 0) {
            throw InvalidAmountException::negative($value);
        }

        if ($value > self::MAX_AMOUNT) {
            throw InvalidAmountException::tooLarge($value, self::MAX_AMOUNT);
        }

        // Warning for very large amounts (not an error, just unusual)
        if ($value > self::RECOMMENDED_MAX) {
            // This is just informational, not thrown
            // Could be logged or handled differently in the future
        }
    }

    /**
     * Validate that amount is positive (not zero)
     *
     * @throws InvalidAmountException
     */
    public static function validatePositive(int|float $value): void
    {
        self::validate($value);

        if ($value === 0 || $value === 0.0) {
            throw InvalidAmountException::zero();
        }

        if ($value < self::MIN_AMOUNT) {
            throw InvalidAmountException::tooSmall($value, self::MIN_AMOUNT);
        }
    }

    /**
     * Format amount for display
     */
    public static function formatForDisplay(int|float $value): string
    {
        return number_format($value, 2, '.', ',');
    }

    /**
     * Check if amount is within recommended range
     */
    public static function isRecommendedRange(int|float $value): bool
    {
        return $value >= self::MIN_AMOUNT && $value <= self::RECOMMENDED_MAX;
    }

    /**
     * Get recommended maximum amount
     */
    public static function getRecommendedMax(): float
    {
        return self::RECOMMENDED_MAX;
    }

    /**
     * Get absolute maximum amount
     */
    public static function getAbsoluteMax(): float
    {
        return self::MAX_AMOUNT;
    }

    /**
     * Get minimum amount
     */
    public static function getMinimum(): float
    {
        return self::MIN_AMOUNT;
    }
}

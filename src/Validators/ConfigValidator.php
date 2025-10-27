<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Validators;

use Farzai\PromptPay\Exceptions\ConfigurationException;

/**
 * Validator for QR code configuration
 * Ensures QR code settings are valid and optimal
 */
final class ConfigValidator
{
    private const MIN_SIZE = 50;
    private const MAX_SIZE = 2000;
    private const RECOMMENDED_MIN_SIZE = 200;
    private const RECOMMENDED_MAX_SIZE = 1000;

    private const MIN_MARGIN = 0;
    private const MAX_MARGIN = 100;
    private const RECOMMENDED_MARGIN = 10;

    /**
     * Validate QR code size
     *
     * @throws ConfigurationException
     */
    public static function validateSize(int $size): void
    {
        if ($size < self::MIN_SIZE) {
            throw ConfigurationException::sizeTooSmall($size, self::MIN_SIZE);
        }

        if ($size > self::MAX_SIZE) {
            throw ConfigurationException::sizeTooLarge($size, self::MAX_SIZE);
        }
    }

    /**
     * Validate QR code margin
     *
     * @throws ConfigurationException
     */
    public static function validateMargin(int $margin): void
    {
        if ($margin < self::MIN_MARGIN) {
            throw ConfigurationException::marginTooSmall($margin, self::MIN_MARGIN);
        }

        if ($margin > self::MAX_MARGIN) {
            throw ConfigurationException::marginTooLarge($margin, self::MAX_MARGIN);
        }
    }

    /**
     * Validate encoding
     *
     * @throws ConfigurationException
     */
    public static function validateEncoding(string $encoding): void
    {
        $validEncodings = ['UTF-8', 'ISO-8859-1', 'ASCII'];

        if (! in_array($encoding, $validEncodings, true)) {
            throw ConfigurationException::invalidEncoding($encoding, $validEncodings);
        }
    }

    /**
     * Check if size is within recommended range
     */
    public static function isRecommendedSize(int $size): bool
    {
        return $size >= self::RECOMMENDED_MIN_SIZE && $size <= self::RECOMMENDED_MAX_SIZE;
    }

    /**
     * Get recommended size
     */
    public static function getRecommendedSize(): int
    {
        return 300; // Default recommended size
    }

    /**
     * Get recommended margin
     */
    public static function getRecommendedMargin(): int
    {
        return self::RECOMMENDED_MARGIN;
    }

    /**
     * Suggest optimal size based on use case
     */
    public static function suggestSize(string $useCase = 'web'): int
    {
        return match ($useCase) {
            'print' => 600,
            'mobile' => 300,
            'web' => 300,
            'thumbnail' => 150,
            'large' => 800,
            default => 300,
        };
    }
}

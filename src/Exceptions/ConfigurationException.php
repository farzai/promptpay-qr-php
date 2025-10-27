<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Exceptions;

/**
 * Exception thrown when configuration is invalid
 */
class ConfigurationException extends PromptPayException
{
    // Error codes
    public const CODE_SIZE_TOO_SMALL = 3001;
    public const CODE_SIZE_TOO_LARGE = 3002;
    public const CODE_MARGIN_TOO_SMALL = 3003;
    public const CODE_MARGIN_TOO_LARGE = 3004;
    public const CODE_INVALID_ENCODING = 3005;
    public const CODE_INVALID_PATH = 3006;
    public const CODE_MISSING_DEPENDENCY = 3007;

    public static function sizeTooSmall(int $size, int $min): self
    {
        return new self(
            sprintf(
                'QR code size %dpx is too small. Minimum size is %dpx. Recommended: 200-1000px.',
                $size,
                $min
            ),
            self::CODE_SIZE_TOO_SMALL
        );
    }

    public static function sizeTooLarge(int $size, int $max): self
    {
        return new self(
            sprintf(
                'QR code size %dpx is too large. Maximum size is %dpx. Recommended: 200-1000px.',
                $size,
                $max
            ),
            self::CODE_SIZE_TOO_LARGE
        );
    }

    public static function marginTooSmall(int $margin, int $min): self
    {
        return new self(
            sprintf(
                'QR code margin %dpx is too small. Minimum margin is %dpx. Recommended: 10px.',
                $margin,
                $min
            ),
            self::CODE_MARGIN_TOO_SMALL
        );
    }

    public static function marginTooLarge(int $margin, int $max): self
    {
        return new self(
            sprintf(
                'QR code margin %dpx is too large. Maximum margin is %dpx. Recommended: 10-20px.',
                $margin,
                $max
            ),
            self::CODE_MARGIN_TOO_LARGE
        );
    }

    /**
     * @param array<string> $validEncodings
     */
    public static function invalidEncoding(string $encoding, array $validEncodings): self
    {
        return new self(
            sprintf(
                'Invalid encoding "%s". Valid encodings: %s. Recommended: UTF-8.',
                $encoding,
                implode(', ', $validEncodings)
            ),
            self::CODE_INVALID_ENCODING
        );
    }

    public static function invalidPath(string $path): self
    {
        return new self(
            sprintf(
                'Invalid path: %s. Please provide a valid file path with extension (e.g., qr.png).',
                $path
            ),
            self::CODE_INVALID_PATH
        );
    }

    public static function missingDependency(string $dependency, string $installCommand): self
    {
        return new self(
            sprintf(
                "Missing required dependency: %s\nPlease install it via: %s",
                $dependency,
                $installCommand
            ),
            self::CODE_MISSING_DEPENDENCY
        );
    }
}

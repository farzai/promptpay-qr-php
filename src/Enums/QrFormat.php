<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Enums;

use Endroid\QrCode\Writer\ConsoleWriter;
use Endroid\QrCode\Writer\GifWriter;
use Endroid\QrCode\Writer\PdfWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;

/**
 * Supported QR code output formats
 */
enum QrFormat: string
{
    case SVG = 'svg';
    case PNG = 'png';
    case PDF = 'pdf';
    case GIF = 'gif';
    case CONSOLE = 'console';

    /**
     * Get the writer class for this format
     */
    public function getWriterClass(): string
    {
        return match ($this) {
            self::SVG => SvgWriter::class,
            self::PNG => PngWriter::class,
            self::PDF => PdfWriter::class,
            self::GIF => GifWriter::class,
            self::CONSOLE => ConsoleWriter::class,
        };
    }

    /**
     * Create writer instance for this format
     */
    public function createWriter(): WriterInterface
    {
        return match ($this) {
            self::SVG => new SvgWriter,
            self::PNG => new PngWriter,
            self::PDF => new PdfWriter,
            self::GIF => new GifWriter,
            self::CONSOLE => new ConsoleWriter,
        };
    }

    /**
     * Check if format is valid
     */
    public static function isValid(string $format): bool
    {
        return self::tryFrom($format) !== null;
    }

    /**
     * Get format from string
     */
    public static function fromString(string $format): self
    {
        $enum = self::tryFrom(strtolower($format));

        if ($enum === null) {
            throw new \Farzai\PromptPay\Exceptions\UnsupportedFormatException(
                "Unsupported format: {$format}, supported formats are: ".implode(', ', self::values())
            );
        }

        return $enum;
    }

    /**
     * Get all format values
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}

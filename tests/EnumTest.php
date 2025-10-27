<?php

use Endroid\QrCode\Writer\GifWriter;
use Endroid\QrCode\Writer\PdfWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;
use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\Exceptions\UnsupportedFormatException;

describe('QrFormat Enum', function () {
    describe('getWriterClass', function () {
        it('returns correct writer class for SVG', function () {
            expect(QrFormat::SVG->getWriterClass())->toBe(SvgWriter::class);
        });

        it('returns correct writer class for PNG', function () {
            expect(QrFormat::PNG->getWriterClass())->toBe(PngWriter::class);
        });

        it('returns correct writer class for PDF', function () {
            expect(QrFormat::PDF->getWriterClass())->toBe(PdfWriter::class);
        });

        it('returns correct writer class for GIF', function () {
            expect(QrFormat::GIF->getWriterClass())->toBe(GifWriter::class);
        });
    });

    describe('createWriter', function () {
        it('creates SVG writer instance', function () {
            $writer = QrFormat::SVG->createWriter();

            expect($writer)->toBeInstanceOf(WriterInterface::class);
            expect($writer)->toBeInstanceOf(SvgWriter::class);
        });

        it('creates PNG writer instance', function () {
            $writer = QrFormat::PNG->createWriter();

            expect($writer)->toBeInstanceOf(WriterInterface::class);
            expect($writer)->toBeInstanceOf(PngWriter::class);
        });

        it('creates PDF writer instance', function () {
            $writer = QrFormat::PDF->createWriter();

            expect($writer)->toBeInstanceOf(WriterInterface::class);
            expect($writer)->toBeInstanceOf(PdfWriter::class);
        });

        it('creates GIF writer instance', function () {
            $writer = QrFormat::GIF->createWriter();

            expect($writer)->toBeInstanceOf(WriterInterface::class);
            expect($writer)->toBeInstanceOf(GifWriter::class);
        });
    });

    describe('isValid', function () {
        it('returns true for valid format svg', function () {
            expect(QrFormat::isValid('svg'))->toBeTrue();
        });

        it('returns true for valid format png', function () {
            expect(QrFormat::isValid('png'))->toBeTrue();
        });

        it('returns true for valid format pdf', function () {
            expect(QrFormat::isValid('pdf'))->toBeTrue();
        });

        it('returns true for valid format gif', function () {
            expect(QrFormat::isValid('gif'))->toBeTrue();
        });

        it('returns false for invalid format', function () {
            expect(QrFormat::isValid('jpg'))->toBeFalse();
            expect(QrFormat::isValid('bmp'))->toBeFalse();
            expect(QrFormat::isValid('webp'))->toBeFalse();
        });
    });

    describe('fromString', function () {
        it('creates SVG enum from string', function () {
            $format = QrFormat::fromString('svg');

            expect($format)->toBe(QrFormat::SVG);
        });

        it('creates PNG enum from string', function () {
            $format = QrFormat::fromString('png');

            expect($format)->toBe(QrFormat::PNG);
        });

        it('creates PDF enum from string', function () {
            $format = QrFormat::fromString('pdf');

            expect($format)->toBe(QrFormat::PDF);
        });

        it('creates GIF enum from string', function () {
            $format = QrFormat::fromString('gif');

            expect($format)->toBe(QrFormat::GIF);
        });

        it('is case insensitive', function () {
            expect(QrFormat::fromString('PNG'))->toBe(QrFormat::PNG);
            expect(QrFormat::fromString('Svg'))->toBe(QrFormat::SVG);
            expect(QrFormat::fromString('PDF'))->toBe(QrFormat::PDF);
        });

        it('throws exception for unsupported format', function () {
            QrFormat::fromString('jpg');
        })->throws(UnsupportedFormatException::class, 'Unsupported format: jpg');

        it('includes list of supported formats in exception', function () {
            try {
                QrFormat::fromString('jpg');
            } catch (UnsupportedFormatException $e) {
                expect($e->getMessage())->toContain('svg');
                expect($e->getMessage())->toContain('png');
                expect($e->getMessage())->toContain('pdf');
                expect($e->getMessage())->toContain('gif');
            }
        });
    });

    describe('values', function () {
        it('returns all format values', function () {
            $values = QrFormat::values();

            expect($values)->toBeArray();
            expect($values)->toContain('svg');
            expect($values)->toContain('png');
            expect($values)->toContain('pdf');
            expect($values)->toContain('gif');
            expect($values)->toContain('console');
        });

        it('returns exactly 5 formats', function () {
            $values = QrFormat::values();

            expect(count($values))->toBe(5);
        });
    });

    describe('enum values', function () {
        it('has correct string value for SVG', function () {
            expect(QrFormat::SVG->value)->toBe('svg');
        });

        it('has correct string value for PNG', function () {
            expect(QrFormat::PNG->value)->toBe('png');
        });

        it('has correct string value for PDF', function () {
            expect(QrFormat::PDF->value)->toBe('pdf');
        });

        it('has correct string value for GIF', function () {
            expect(QrFormat::GIF->value)->toBe('gif');
        });

        it('has correct string value for CONSOLE', function () {
            expect(QrFormat::CONSOLE->value)->toBe('console');
        });
    });
});

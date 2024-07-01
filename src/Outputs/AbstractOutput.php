<?php

namespace Farzai\PromptPay\Outputs;

use Endroid\QrCode\Writer\GifWriter;
use Endroid\QrCode\Writer\PdfWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;
use Farzai\PromptPay\Contracts\OutputInterface;

abstract class AbstractOutput implements OutputInterface
{
    protected array $writers = [
        'svg' => SvgWriter::class,
        'png' => PngWriter::class,
        'pdf' => PdfWriter::class,
        'gif' => GifWriter::class,
    ];

    protected function createWriter(string $writer): WriterInterface
    {
        if (! array_key_exists($writer, $this->writers)) {
            throw new \Exception("Unsupported format: {$writer}, supported formats are: ".implode(', ', array_keys($this->writers)).'.');
        }

        $writer = new $this->writers[$writer];

        if (! $writer instanceof WriterInterface) {
            throw new \Exception('Invalid writer');
        }

        return $writer;
    }
}

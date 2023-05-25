<?php

namespace Farzai\PromptPay;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\ConsoleWriter;
use Endroid\QrCode\Writer\PngWriter;
use Farzai\PromptPay\Contracts\QrCode as Contract;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QrCode implements Contract
{
    private string $payload;

    /**
     * QrCode constructor.
     */
    public function __construct(string $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Write qr code to file
     */
    public function save(string $path): void
    {
        $this->createBuilder()
            ->build()
            ->saveToFile($path);
    }

    /**
     * Convert qr to data uri
     */
    public function asDataUri(): string
    {
        return $this->asBase64();
    }

    /**
     * Convert qr to base64
     */
    public function asBase64(): string
    {
        return $this->createBuilder()
            ->build()
            ->getDataUri();
    }

    public function asPng(): string
    {
        return $this->createBuilder()
            ->build()
            ->getString();
    }

    /**
     * Convert qr to response
     */
    public function toPsrResponse(): ResponseInterface
    {
        $qrCode = $this->createBuilder()->build();

        return new Response(
            200,
            ['Content-Type' => $qrCode->getMimeType()],
            $qrCode->getString()
        );
    }

    /**
     * Convert qr to console
     */
    public function asConsole(OutputInterface $output): void
    {
        $output->writeln(
            $this
                ->createBuilder()
                ->writer(new ConsoleWriter())
                ->encoding(new Encoding('UTF-8'))
                ->build()
                ->getString()
        );
    }

    public function __toString()
    {
        return $this->payload;
    }

    private function createBuilder(): BuilderInterface
    {
        return Builder::create()
            ->writer(new PngWriter())
            ->data($this->payload)
            ->size(500)
            ->margin(0);
    }
}

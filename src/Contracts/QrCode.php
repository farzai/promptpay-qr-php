<?php

namespace Farzai\PromptPay\Contracts;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface QrCode
{
    /**
     * Write qr code to file
     */
    public function save(string $path): void;

    /**
     * Convert qr to data uri
     */
    public function asDataUri(): string;

    /**
     * Convert qr to base64
     */
    public function asBase64(): string;

    /**
     * Convert qr to png
     */
    public function asPng(): string;

    /**
     * Convert qr to response
     */
    public function toPsrResponse(): PsrResponseInterface;

    /**
     * Convert qr to console
     */
    public function asConsole(OutputInterface $output): void;
}

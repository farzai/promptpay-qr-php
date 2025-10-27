<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Outputs;

use Farzai\PromptPay\Contracts\OutputInterface;
use Farzai\PromptPay\Contracts\QrCodeBuilder;
use Farzai\PromptPay\Enums\QrFormat;
use Symfony\Component\Console\Output\OutputInterface as SymfonyOutputInterface;

/**
 * Console Output - Displays QR codes in terminal
 *
 * Uses the QrCodeBuilder abstraction for consistency.
 * Note: Console format is a specialized format that renders ASCII art.
 */
class ConsoleOutput implements OutputInterface
{
    public function __construct(
        private readonly QrCodeBuilder $qrCodeBuilder,
        private readonly SymfonyOutputInterface $output
    ) {}

    public function write(string $payload): string
    {
        // Build QR code with console-specific format (ASCII art)
        $qrCode = $this->qrCodeBuilder->build($payload, QrFormat::CONSOLE);

        $result = $qrCode->getString();
        $this->output->writeln($result);

        return $result;
    }
}

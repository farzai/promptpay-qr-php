<?php

declare(strict_types=1);

namespace Farzai\PromptPay;

use Farzai\PromptPay\Contracts\OutputInterface;
use Farzai\PromptPay\Contracts\QrCode as Contract;

class QrCode implements Contract
{
    /**
     * QrCode constructor.
     */
    public function __construct(
        private readonly string $payload
    ) {}

    /**
     * Send qr code to output
     */
    public function writeTo(OutputInterface $output): mixed
    {
        return $output->write($this->payload);
    }

    /**
     * Get qr code payload
     */
    public function __toString(): string
    {
        return $this->payload;
    }
}

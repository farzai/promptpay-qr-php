<?php

namespace Farzai\PromptPay;

use Farzai\PromptPay\Contracts\OutputInterface;
use Farzai\PromptPay\Contracts\QrCode as Contract;

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
     * Send qr code to output
     */
    public function writeTo(OutputInterface $output): mixed
    {
        return $output->write($this->payload);
    }

    /**
     * Get qr code payload
     */
    public function __toString()
    {
        return $this->payload;
    }
}

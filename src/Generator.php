<?php

namespace Farzai\PromptPay;

use Farzai\PromptPay\Contracts\PayloadGenerator as PayloadGeneratorContract;
use Farzai\PromptPay\Contracts\QrCode as QrCodeContract;

class Generator
{
    private PayloadGeneratorContract $payloadGenerator;

    public function __construct()
    {
        $this->payloadGenerator = new PayloadGenerator(
            new CRC16CCITTAlgorithm
        );
    }

    /**
     * Generate qr code
     */
    public function generate(string $recipient, $amount = null): QrCodeContract
    {
        // Remove non-numeric characters
        $recipient = preg_replace('/\D/', '', $recipient);

        // Validate target
        if (! preg_match('/^[0-9]{10,15}$/', $recipient)) {
            throw new \InvalidArgumentException('Invalid recipient, must be 10-15 digits');
        }

        $payload = $this->payloadGenerator->generate(
            $recipient, $amount,
        );

        return new QrCode($payload);
    }
}

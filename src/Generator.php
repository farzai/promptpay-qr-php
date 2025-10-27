<?php

declare(strict_types=1);

namespace Farzai\PromptPay;

use Farzai\PromptPay\Contracts\Generator as GeneratorContract;
use Farzai\PromptPay\Contracts\PayloadGenerator as PayloadGeneratorContract;
use Farzai\PromptPay\Contracts\QrCode as QrCodeContract;

class Generator implements GeneratorContract
{
    public function __construct(
        private readonly PayloadGeneratorContract $payloadGenerator
    ) {}

    /**
     * Generate qr code
     *
     * @param  int|float|null  $amount
     */
    public function generate(string $recipient, $amount = null): QrCodeContract
    {
        // Validation is now handled in Recipient value object
        $payload = $this->payloadGenerator->generate($recipient, $amount);

        return new QrCode($payload);
    }
}

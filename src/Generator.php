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
            new CRC16CCITTAlgorithm()
        );
    }

    /**
     * @param  int|float  $amount
     */
    public function generate(string $target, $amount = null): QrCodeContract
    {
        $payload = $this->payloadGenerator->generate(
            $target, $amount,
        );

        return new QrCode($payload);
    }
}

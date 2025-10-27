<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Contracts;

interface Generator
{
    /**
     * Generate qr code
     *
     * @param  int|float|null  $amount
     */
    public function generate(string $recipient, $amount = null): QrCode;
}

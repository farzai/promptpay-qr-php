<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Contracts;

interface QrCode
{
    /**
     * Write qr code to output
     */
    public function writeTo(OutputInterface $output): mixed;

    /**
     * Get qr code payload
     */
    public function __toString(): string;
}

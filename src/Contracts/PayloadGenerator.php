<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Contracts;

interface PayloadGenerator
{
    /**
     * Generate payload
     *
     * @param  int|float|null  $amount
     */
    public function generate(string $target, $amount = null): string;
}

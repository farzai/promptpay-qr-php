<?php

namespace Farzai\PromptPay\Contracts;

interface PayloadGenerator
{
    /**
     * Generate payload
     *
     * @param  int|float  $amount
     */
    public function generate(string $target, $amount = null): string;
}

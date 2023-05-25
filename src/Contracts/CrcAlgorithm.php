<?php

namespace Farzai\PromptPay\Contracts;

interface CrcAlgorithm
{
    public function generate(string $data): string;
}

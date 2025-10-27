<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Contracts;

interface CrcAlgorithm
{
    public function generate(string $data): string;
}

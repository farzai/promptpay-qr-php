<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Contracts;

interface OutputInterface
{
    /**
     * Write payload to output
     */
    public function write(string $payload): mixed;
}

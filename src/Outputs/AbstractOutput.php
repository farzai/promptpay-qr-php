<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Outputs;

use Farzai\PromptPay\Contracts\OutputInterface;
use Farzai\PromptPay\Contracts\QrCodeBuilder;

abstract class AbstractOutput implements OutputInterface
{
    public function __construct(
        protected readonly QrCodeBuilder $qrCodeBuilder
    ) {}
}

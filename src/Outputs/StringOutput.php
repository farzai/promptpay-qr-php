<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Outputs;

use Farzai\PromptPay\Contracts\QrCodeBuilder;
use Farzai\PromptPay\Enums\QrFormat;

class StringOutput extends AbstractOutput
{
    public function __construct(
        QrCodeBuilder $qrCodeBuilder,
        private readonly QrFormat $format
    ) {
        parent::__construct($qrCodeBuilder);
    }

    public function write(string $payload): string
    {
        $qrCode = $this->qrCodeBuilder->build($payload, $this->format);

        return $qrCode->getString();
    }
}

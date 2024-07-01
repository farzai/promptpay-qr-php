<?php

namespace Farzai\PromptPay\Outputs;

use Endroid\QrCode\Builder\Builder;

class DataUriOutput extends AbstractOutput
{
    public function __construct(private string $format = 'png') {}

    public function write(string $payload): mixed
    {
        $qrCode = Builder::create()
            ->writer($this->createWriter($this->format))
            ->data($payload)
            ->size(100)
            ->margin(0)
            ->build();

        return $qrCode->getDataUri();
    }
}

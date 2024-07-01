<?php

namespace Farzai\PromptPay\Outputs;

use Endroid\QrCode\Builder\Builder;
use Farzai\Transport\ResponseFactory;

class HttpResponseOutput extends AbstractOutput
{
    /**
     * Write the payload to the output.
     */
    public function write(string $payload): mixed
    {
        if (! class_exists(ResponseFactory::class)) {
            throw new \RuntimeException('The ResponseOutput is required farzai/transport package. Please install it via composer require farzai/transport.');
        }

        $qrCode = Builder::create()
            ->writer($this->createWriter('png'))
            ->data($payload)
            ->size(100)
            ->margin(0)
            ->build();

        return ResponseFactory::create(
            200,
            ['Content-Type' => $qrCode->getMimeType()],
            $qrCode->getString()
        );
    }
}

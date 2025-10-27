<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Outputs;

use Farzai\PromptPay\Contracts\QrCodeBuilder;
use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\Exceptions\ConfigurationException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HttpResponseOutput extends AbstractOutput
{
    public function __construct(
        QrCodeBuilder $qrCodeBuilder,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory
    ) {
        parent::__construct($qrCodeBuilder);
    }

    /**
     * Write the payload to the output.
     */
    public function write(string $payload): ResponseInterface
    {
        $qrCode = $this->qrCodeBuilder->build($payload, QrFormat::PNG);
        $stream = $this->streamFactory->createStream($qrCode->getString());

        return $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', $qrCode->getMimeType())
            ->withBody($stream);
    }
}

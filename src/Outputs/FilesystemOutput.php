<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Outputs;

use Farzai\PromptPay\Contracts\QrCodeBuilder;
use Farzai\PromptPay\Enums\QrFormat;
use Farzai\PromptPay\Exceptions\ConfigurationException;

class FilesystemOutput extends AbstractOutput
{
    public function __construct(
        QrCodeBuilder $qrCodeBuilder,
        private readonly string $path
    ) {
        parent::__construct($qrCodeBuilder);
    }

    public function write(string $payload): string
    {
        // Get extension from path
        $extension = pathinfo($this->path, PATHINFO_EXTENSION);

        if (empty($extension)) {
            throw new ConfigurationException('Invalid path: no file extension found');
        }

        $format = QrFormat::fromString($extension);
        $qrCode = $this->qrCodeBuilder->build($payload, $format);

        $qrCode->saveToFile($this->path);

        return $this->path;
    }
}

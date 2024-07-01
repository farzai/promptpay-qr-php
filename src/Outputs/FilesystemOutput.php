<?php

namespace Farzai\PromptPay\Outputs;

use Endroid\QrCode\Builder\Builder;

class FilesystemOutput extends AbstractOutput
{
    public function __construct(
        private string $path
    ) {}

    public function write(string $payload): mixed
    {
        // Get extension from path
        $extension = pathinfo($this->path, PATHINFO_EXTENSION);

        if (empty($extension)) {
            throw new \Exception('Invalid path');
        }

        $qrCode = Builder::create()
            ->writer($this->createWriter($extension))
            ->data($payload)
            ->size(100)
            ->margin(0)
            ->build();

        $qrCode->saveToFile($this->path);

        return $this->path;
    }
}

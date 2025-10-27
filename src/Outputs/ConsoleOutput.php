<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Outputs;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\ConsoleWriter;
use Farzai\PromptPay\Contracts\OutputInterface;
use Farzai\PromptPay\Contracts\QrCodeBuilder;
use Symfony\Component\Console\Output\OutputInterface as SymfonyOutputInterface;

class ConsoleOutput implements OutputInterface
{
    public function __construct(
        private readonly QrCodeBuilder $qrCodeBuilder,
        private readonly SymfonyOutputInterface $output
    ) {}

    public function write(string $payload): string
    {
        $config = $this->qrCodeBuilder->getConfig();

        // ConsoleWriter requires special handling
        $builder = Builder::create()
            ->writer(new ConsoleWriter)
            ->data($payload)
            ->encoding(new Encoding($config->getEncoding()))
            ->size($config->getSize())
            ->margin($config->getMargin());

        $qrCode = $builder->build();

        $result = $qrCode->getString();
        $this->output->writeln($result);

        return $result;
    }
}

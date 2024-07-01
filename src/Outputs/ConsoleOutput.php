<?php

namespace Farzai\PromptPay\Outputs;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\ConsoleWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Farzai\PromptPay\Contracts\OutputInterface;
use Symfony\Component\Console\Output\OutputInterface as SymfonyOutputInterface;

class ConsoleOutput implements OutputInterface
{
    public function __construct(
        private SymfonyOutputInterface $output
    ) {}

    public function write(string $payload): mixed
    {
        $qrCode = Builder::create()
            ->writer(new SvgWriter)
            ->data($payload)
            ->size(100)
            ->margin(0)
            ->writer(new ConsoleWriter())
            ->encoding(new Encoding('UTF-8'))
            ->build();

        $this->output->writeln($result = $qrCode->getString());

        return $result;
    }
}

<?php

namespace Farzai\PromptPay;

use Farzai\PromptPay\Contracts\QrCode;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PromptPay
{
    private Generator $generator;

    /**
     * Create qr code
     */
    public static function create(string $recipient, $amount = null): QrCode
    {
        return (new self($recipient, $amount))->build();
    }

    /**
     * Start creating qr code
     */
    public static function to(string $recipient): self
    {
        return new self($recipient);
    }

    /**
     * Set amount
     *
     * @param  mixed  $amount
     */
    public function amount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Build qr code
     */
    public function build(): QrCode
    {
        return $this->generator->generate(
            recipient: $this->recipient,
            amount: $this->amount,
        );
    }

    /**
     * Get qr code as data uri
     */
    public function toDataUri(string $format): string
    {
        return $this->build()->writeTo(new Outputs\DataUriOutput($format));
    }

    /**
     * Save qr code to filesystem
     */
    public function toFile(string $path): void
    {
        $this->build()->writeTo(new Outputs\FilesystemOutput($path));
    }

    /**
     * Response qr code as http response
     */
    public function respond(): ResponseInterface
    {
        return $this->build()->writeTo(new Outputs\HttpResponseOutput);
    }

    /**
     * Write qr code to console
     */
    public function toConsole(OutputInterface $output): void
    {
        $this->build()->writeTo(new Outputs\ConsoleOutput($output));
    }

    private function __construct(
        private string $recipient,
        private $amount = null
    ) {
        $this->generator = new Generator;
    }
}

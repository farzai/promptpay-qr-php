<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Commands;

use Farzai\PromptPay\Exceptions\InvalidAmountException;
use Farzai\PromptPay\Exceptions\InvalidRecipientException;
use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\ValueObjects\Amount;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Farzai\PromptPay\ValueObjects\Recipient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateQrCode extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('create-qr-code')
            ->setDescription('Create a PromptPay QR code for receiving payments')
            ->addArgument('target', InputArgument::OPTIONAL, 'Target (phone number, tax ID, or e-wallet ID)')
            ->addArgument('amount', InputArgument::OPTIONAL, 'Amount to receive (optional for static QR)')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Save QR code to file (e.g., qrcode.png, qrcode.svg)')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format for data URI: png or svg', 'png')
            ->addOption('size', 's', InputOption::VALUE_REQUIRED, 'QR code size in pixels', '300')
            ->addOption('show-payload', 'p', InputOption::VALUE_NONE, 'Display the raw PromptPay payload string')
            ->setHelp($this->getHelpText());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get arguments
        $targetInput = $input->getArgument('target');
        $amountInput = $input->getArgument('amount');

        // Get options
        $outputFile = $input->getOption('output');
        $format = $input->getOption('format');
        $size = (int) $input->getOption('size');
        $showPayload = $input->getOption('show-payload');

        // Prompt for target if not provided
        if (! $targetInput) {
            $targetInput = $this->ask(
                $this->getDefinition()->getArgument('target')->getDescription(),
                $input,
                $output
            );

            if (! $targetInput) {
                $output->writeln('<error>Please enter a receiver target, e.g., 0899999999</error>');

                return Command::FAILURE;
            }
        }

        // Validate and normalize recipient using existing value object
        try {
            $recipient = Recipient::fromString($targetInput);
        } catch (InvalidRecipientException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return Command::FAILURE;
        }

        // Validate amount if provided
        $amount = null;
        if ($amountInput !== null && $amountInput !== '') {
            try {
                $amount = Amount::fromNumeric((float) $amountInput);
            } catch (InvalidAmountException $e) {
                $output->writeln('<error>'.$e->getMessage().'</error>');

                return Command::FAILURE;
            }
        }

        // Display header information
        $this->displayHeader($output, $recipient, $amount);

        // Build PromptPay QR code using modern API
        $builder = PromptPay::generate($recipient->getValue())
            ->withAmount($amount?->getValue());

        // Apply size configuration if specified
        if ($size > 0 && $size !== 300) {
            $builder = $builder->withConfig(QrCodeConfig::create($size));
        }

        // Handle output file option
        if ($outputFile) {
            $result = $builder->toFile($outputFile);
            $savedPath = $result->getPath() ?? $outputFile;
            $output->writeln("<info>QR code saved to: {$savedPath}</info>");
            $output->writeln('');
        } else {
            // Display QR code in console
            $builder->toConsole($output);
            $output->writeln('');
        }

        // Show payload if requested
        if ($showPayload) {
            $payload = $builder->toPayload();
            $output->writeln('<comment>Raw Payload:</comment>');
            $output->writeln($payload);
            $output->writeln('');
        }

        return Command::SUCCESS;
    }

    /**
     * Display header information with recipient details
     */
    private function displayHeader(OutputInterface $output, Recipient $recipient, ?Amount $amount): void
    {
        $lines = [
            '<info>PromptPay QR Code</info>',
            '==============================================',
            sprintf('Recipient: %s', $recipient->getDisplayValue()),
            sprintf('Type: %s', $this->getRecipientTypeLabel($recipient)),
        ];

        if ($amount !== null) {
            $lines[] = sprintf('Amount: %s THB', $amount->getDisplayValue());
        } else {
            $lines[] = 'Amount: Static QR (any amount)';
        }

        $lines[] = '==============================================';
        $lines[] = '';

        $output->writeln($lines);
    }

    /**
     * Get human-readable label for recipient type
     */
    private function getRecipientTypeLabel(Recipient $recipient): string
    {
        return match ($recipient->getType()->value) {
            '01' => 'Phone Number',
            '02' => 'Tax ID / National ID',
            '03' => 'E-Wallet ID',
        };
    }

    /**
     * Get help text with examples
     */
    private function getHelpText(): string
    {
        return <<<'HELP'
Create a PromptPay QR code for receiving payments.

<info>Examples:</info>
  # Generate QR code for phone number
  <comment>php bin/promptpay 0899999999</comment>

  # Generate QR code with specific amount
  <comment>php bin/promptpay 0899999999 100.50</comment>

  # Accept flexible input formats (dashes will be removed)
  <comment>php bin/promptpay 089-999-9999 100</comment>

  # Generate QR for Tax ID (13 digits)
  <comment>php bin/promptpay 1234567890123</comment>

  # Save QR code to PNG file
  <comment>php bin/promptpay 0899999999 --output=qrcode.png</comment>

  # Save QR code to SVG file with custom size
  <comment>php bin/promptpay 0899999999 --output=qrcode.svg --size=500</comment>

  # Display the raw PromptPay payload string
  <comment>php bin/promptpay 0899999999 --show-payload</comment>

<info>Supported Recipient Types:</info>
  • Phone Number: 10 digits (e.g., 0899999999)
  • Tax ID / National ID: 13 digits (e.g., 1234567890123)
  • E-Wallet ID: 15 digits (e.g., 123456789012345)

<info>Options:</info>
  -o, --output=FILE       Save QR code to file (.png or .svg)
  -f, --format=FORMAT     Output format: png or svg (default: png)
  -s, --size=SIZE         QR code size in pixels (default: 300)
  -p, --show-payload      Display the raw PromptPay payload string
HELP;
    }

    /**
     * Ask a question to the user.
     */
    private function ask(string $question, InputInterface $input, OutputInterface $output): ?string
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return $helper->ask(
            $input, $output, new Question("Enter {$question}: ")
        );
    }
}

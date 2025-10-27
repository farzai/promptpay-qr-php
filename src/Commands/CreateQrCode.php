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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
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
                $this->displayError(
                    $output,
                    'Missing recipient information',
                    'Please provide a valid recipient (phone number, tax ID, or e-wallet ID)',
                    [
                        'Phone Number: 10 digits (e.g., 0899999999)',
                        'Tax ID / National ID: 13 digits (e.g., 1234567890123)',
                        'E-Wallet ID: 15 digits (e.g., 123456789012345)',
                    ]
                );

                return Command::FAILURE;
            }
        }

        // Validate and normalize recipient using existing value object
        try {
            $recipient = Recipient::fromString($targetInput);
        } catch (InvalidRecipientException $e) {
            $this->displayError(
                $output,
                'Invalid Recipient',
                $e->getMessage(),
                [
                    'Phone numbers must be exactly 10 digits',
                    'Tax IDs / National IDs must be exactly 13 digits',
                    'E-Wallet IDs must be exactly 15 digits',
                    'Remove any dashes, spaces, or special characters',
                ]
            );

            return Command::FAILURE;
        }

        // Validate amount if provided
        $amount = null;
        if ($amountInput !== null && $amountInput !== '') {
            try {
                $amount = Amount::fromNumeric((float) $amountInput);
            } catch (InvalidAmountException $e) {
                $this->displayError(
                    $output,
                    'Invalid Amount',
                    $e->getMessage(),
                    [
                        'Amount must be a positive number',
                        'Use decimal point for cents (e.g., 100.50)',
                        'Maximum 2 decimal places allowed',
                        'Leave empty for static QR (any amount)',
                    ]
                );

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

        // Get payload for details display
        $payload = $builder->toPayload();

        // Display QR code technical details
        $this->displayQrCodeDetails($output, $payload, $size, $format);

        // Handle output file option
        if ($outputFile) {
            $this->displaySectionHeader($output, 'Generating QR Code');
            $output->writeln('  <comment>Processing...</comment>');
            $output->writeln('');

            $result = $builder->toFile($outputFile);
            $savedPath = $result->getPath() ?? $outputFile;

            // Display success message with file details
            if (file_exists($savedPath)) {
                $fileSize = filesize($savedPath);
                $fileSizeFormatted = $this->formatFileSize($fileSize !== false ? $fileSize : 0);
                $absolutePath = realpath($savedPath);

                $this->displaySectionHeader($output, 'QR Code Generated Successfully');

                $table = $this->createInfoTable($output);
                $table->setRows([
                    $this->formatInfoRow('Status', '<fg=green>✓ Success</>'),
                    new TableSeparator,
                    $this->formatInfoRow('File Path', $absolutePath ?: $savedPath),
                    new TableSeparator,
                    $this->formatInfoRow('File Size', $fileSizeFormatted),
                    new TableSeparator,
                    $this->formatInfoRow('Format', strtoupper(pathinfo($savedPath, PATHINFO_EXTENSION))),
                ]);
                $table->render();
                $output->writeln('');
            } else {
                $output->writeln($this->formatSuccess("QR code saved to: {$savedPath}"));
                $output->writeln('');
            }
        } else {
            // Display QR code in console
            $this->displaySectionHeader($output, 'QR Code');
            $output->writeln('');
            $builder->toConsole($output);
            $output->writeln('');
        }

        // Show full payload if requested
        if ($showPayload) {
            $this->displaySectionHeader($output, 'Raw PromptPay Payload');
            $output->writeln('');
            $output->writeln("  <fg=yellow>{$payload}</>");
            $output->writeln('');
        }

        // Display summary and tips
        $this->displaySummary($output, $amount, (bool) $outputFile);

        return Command::SUCCESS;
    }

    /**
     * Display header information with recipient details
     */
    private function displayHeader(OutputInterface $output, Recipient $recipient, ?Amount $amount): void
    {
        $this->displaySectionHeader($output, 'PromptPay QR Code Generation');

        $table = $this->createInfoTable($output);

        $rows = [
            $this->formatInfoRow('Recipient', $recipient->getDisplayValue()),
            new TableSeparator,
            $this->formatInfoRow('Recipient Type', $this->getRecipientTypeLabel($recipient)),
            new TableSeparator,
        ];

        if ($amount !== null) {
            $rows[] = $this->formatInfoRow('Amount', $amount->getDisplayValue().' THB');
            $rows[] = new TableSeparator;
            $rows[] = $this->formatInfoRow('QR Type', '<fg=green>Dynamic</> (Fixed Amount)');
        } else {
            $rows[] = $this->formatInfoRow('Amount', '<fg=cyan>Any Amount</>');
            $rows[] = new TableSeparator;
            $rows[] = $this->formatInfoRow('QR Type', '<fg=blue>Static</> (Flexible Amount)');
        }

        $table->setRows($rows);
        $table->render();

        $output->writeln('');
    }

    /**
     * Display QR code technical details
     */
    private function displayQrCodeDetails(OutputInterface $output, string $payload, int $size, string $format): void
    {
        $this->displaySectionHeader($output, 'QR Code Details');

        $table = $this->createInfoTable($output);

        $payloadLength = strlen($payload);
        $payloadPreview = substr($payload, 0, 40).'...'.substr($payload, -10);

        $table->setRows([
            $this->formatInfoRow('Payload Length', $payloadLength.' characters'),
            new TableSeparator,
            $this->formatInfoRow('QR Code Size', $size.' × '.$size.' pixels'),
            new TableSeparator,
            $this->formatInfoRow('Output Format', strtoupper($format)),
            new TableSeparator,
            $this->formatInfoRow('Error Correction', 'Level L (7% damage tolerance)'),
            new TableSeparator,
            $this->formatInfoRow('Payload Preview', $payloadPreview),
        ]);

        $table->render();
        $output->writeln('');
    }

    /**
     * Display summary with helpful tips
     */
    private function displaySummary(OutputInterface $output, ?Amount $amount, bool $savedToFile): void
    {
        $this->displaySectionHeader($output, 'Summary & Next Steps');

        $output->writeln('  <fg=green>✓</> QR code has been generated successfully');
        $output->writeln('');

        // Display tips based on QR type
        if ($amount !== null) {
            $output->writeln('  <fg=cyan>💡 Tips for Dynamic QR Codes (Fixed Amount):</>');
            $output->writeln('  • This QR code is for a specific amount of <fg=white;options=bold>'.$amount->getDisplayValue().' THB</>');
            $output->writeln('  • Payer cannot change the amount when scanning');
            $output->writeln('  • Perfect for invoices, fixed-price items, or bills');
        } else {
            $output->writeln('  <fg=cyan>💡 Tips for Static QR Codes (Flexible Amount):</>');
            $output->writeln('  • Payer can enter any amount when scanning');
            $output->writeln('  • Reusable for multiple transactions');
            $output->writeln('  • Ideal for donations, tips, or variable payments');
        }

        $output->writeln('');

        // Display integration tips
        if ($savedToFile) {
            $output->writeln('  <fg=yellow>📱 How to use this QR code:</>');
            $output->writeln('  • Print it on receipts, invoices, or flyers');
            $output->writeln('  • Display it on your website or app');
            $output->writeln('  • Share it via email or messaging apps');
        } else {
            $output->writeln('  <fg=yellow>📱 How to test this QR code:</>');
            $output->writeln('  • Open any Thai banking app with PromptPay support');
            $output->writeln('  • Scan the QR code displayed above');
            $output->writeln('  • Verify the recipient and amount details');
        }

        $output->writeln('');
        $output->writeln('  <fg=gray>For more options, run:</> <fg=white;options=bold>php bin/promptpay --help</>');
        $output->writeln('');
    }

    /**
     * Display a formatted error message with helpful suggestions
     *
     * @param  string[]  $suggestions
     */
    private function displayError(OutputInterface $output, string $title, string $message, array $suggestions = []): void
    {
        $output->writeln('');
        $output->writeln('  <fg=red;options=bold>✗ Error: '.$title.'</>');
        $output->writeln('  <fg=red>'.$message.'</>');

        if (! empty($suggestions)) {
            $output->writeln('');
            $output->writeln('  <fg=yellow>Suggestions:</>');
            foreach ($suggestions as $suggestion) {
                $output->writeln('  <fg=gray>•</> '.$suggestion);
            }
        }

        $output->writeln('');
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

    /**
     * Display a styled section header with border
     */
    private function displaySectionHeader(OutputInterface $output, string $title): void
    {
        $length = mb_strlen($title) + 4;
        $border = str_repeat('─', $length);

        $output->writeln('');
        $output->writeln("<fg=cyan>┌{$border}┐</>");
        $output->writeln("<fg=cyan>│</> <fg=cyan;options=bold>{$title}</> <fg=cyan>│</>");
        $output->writeln("<fg=cyan>└{$border}┘</>");
    }

    /**
     * Create a styled table for displaying key-value information
     */
    private function createInfoTable(OutputInterface $output): Table
    {
        $table = new Table($output);
        $table->setStyle('compact');
        $table->setColumnWidths([20, 50]);

        return $table;
    }

    /**
     * Format a success message with icon
     */
    private function formatSuccess(string $message): string
    {
        return "<fg=green>✓</> <info>{$message}</info>";
    }

    /**
     * Format an info label with value
     *
     * @return array{string, string}
     */
    private function formatInfoRow(string $label, string $value): array
    {
        return [
            "<fg=yellow>{$label}</>",
            "<fg=white;options=bold>{$value}</>",
        ];
    }

    /**
     * Format file size in human-readable format
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}

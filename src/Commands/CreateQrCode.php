<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Commands;

use Farzai\PromptPay\PromptPay;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateQrCode extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('create-qr-code')
            ->setDescription('Create QR Code PromptPay for receive')
            ->addArgument('target', InputArgument::OPTIONAL, 'Target (phone number, citizen id, e-wallet id)')
            ->addArgument('amount', InputArgument::OPTIONAL, 'Amount to receive');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $input->getArgument('target');
        $amount = $input->getArgument('amount') ?? null;

        if (! $target) {
            $target = $this->ask(
                $this->getDefinition()->getArgument('target')->getDescription(),
                $input,
                $output
            );

            if (! $target) {
                $output->writeln('Please enter receiver target., e.g. 0899999999');

                return Command::FAILURE;
            }
        }

        $lines = [
            "QR Code PromptPay for: {$target}",
        ];

        if ($amount) {
            $lines[] = 'Amount: '.number_format($amount, 2);
        }

        $output->writeln([
            ...$lines,
            '==============================================',
            '',
        ]);

        PromptPay::to($target)
            ->amount($amount)
            ->toConsole($output);

        return Command::SUCCESS;
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

<?php

namespace Farzai\PromptPay\Commands;

use Farzai\PromptPay\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateQrCode extends Command
{
    protected static $defaultName = 'farzai:promptpay:create-qr-code';

    protected function configure()
    {
        $this
            ->setDescription('Create QR Code PromptPay for receive')
            ->addArgument('target', InputArgument::OPTIONAL, 'Target (phone number, citizen id, e-wallet id)')
            ->addOption('amount', 'a', InputOption::VALUE_OPTIONAL, 'Amount (optional)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('target');
        $amount = $input->getOption('amount') ?? null;

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
            '====================================',
            '',
        ]);

        $generator = new Generator();

        $generator
            ->generate($target, $amount)
            ->asConsole($output);

        return Command::SUCCESS;
    }

    /**
     * Ask a question to the user.
     *
     * @return string
     */
    private function ask(string $question, InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return $helper->ask(
            $input, $output, new Question("Enter {$question}: ")
        );
    }
}

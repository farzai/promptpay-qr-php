<?php

/**
 * Example 6: Symfony Integration
 *
 * This example demonstrates how to integrate PromptPay QR codes in Symfony applications.
 * Note: This is a conceptual example showing Symfony-specific patterns.
 */

require __DIR__.'/../vendor/autoload.php';

use Farzai\PromptPay\PromptPay;

echo "=== Symfony Integration Examples ===\n\n";

// ====================
// Example 1: Controller with PSR-7
// ====================
echo "1. Symfony Controller Example (with PSR-7):\n";
echo "   File: src/Controller/PaymentController.php\n\n";

echo <<<'PHP'
<?php

namespace App\Controller;

use Farzai\PromptPay\Exceptions\InvalidAmountException;
use Farzai\PromptPay\Exceptions\InvalidRecipientException;
use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/qrcode', name: 'payment_qrcode', methods: ['GET'])]
    public function showQrCode(Request $request): Response
    {
        $recipient = $this->getParameter('app.promptpay_recipient');
        $amount = $request->query->get('amount', 100);

        try {
            $qrCode = PromptPay::qrCode($recipient, (float) $amount)
                ->toDataUri('png');

            return $this->render('payment/qrcode.html.twig', [
                'qr_code' => $qrCode->getData(),
                'recipient' => $recipient,
                'amount' => $amount,
            ]);
        } catch (InvalidRecipientException | InvalidAmountException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('payment_form');
        }
    }

    #[Route('/api/generate', name: 'payment_api_generate', methods: ['POST'])]
    public function generateQrCode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $recipient = $data['recipient'] ?? null;
        $amount = $data['amount'] ?? null;

        if (!$recipient || !$amount) {
            return $this->json([
                'success' => false,
                'message' => 'Recipient and amount are required',
            ], 400);
        }

        try {
            $config = QrCodeConfig::create(
                size: (int) ($data['size'] ?? 300),
                margin: (int) ($data['margin'] ?? 10)
            );

            $result = PromptPay::qrCode($recipient, (float) $amount)
                ->withConfig($config)
                ->toDataUri('png');

            return $this->json([
                'success' => true,
                'qr_code' => $result->getData(),
                'format' => $result->getFormat()->value,
                'size' => $result->getSize(),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 400);
        }
    }

    #[Route('/download/{recipient}/{amount}', name: 'payment_download', methods: ['GET'])]
    public function downloadQrCode(string $recipient, float $amount): Response
    {
        try {
            // Create PSR-17 factories
            $psr17Factory = new Psr17Factory();

            // Generate QR code as PSR-7 response
            $psrResponse = PromptPay::qrCode($recipient, $amount)
                ->toResponse($psr17Factory, $psr17Factory);

            // Convert PSR-7 response to Symfony response
            $response = new Response(
                (string) $psrResponse->getBody(),
                $psrResponse->getStatusCode()
            );

            foreach ($psrResponse->getHeaders() as $name => $values) {
                $response->headers->set($name, $values);
            }

            $response->headers->set('Content-Disposition', 'attachment; filename="promptpay-qr.png"');

            return $response;
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage());
        }
    }
}

PHP;

echo "\n\n";

// ====================
// Example 2: Service Class
// ====================
echo "2. Symfony Service Example:\n";
echo "   File: src/Service/PromptPayService.php\n\n";

echo <<<'PHP'
<?php

namespace App\Service;

use Farzai\PromptPay\Exceptions\InvalidAmountException;
use Farzai\PromptPay\Exceptions\InvalidRecipientException;
use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\ValueObjects\OutputResult;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class PromptPayService
{
    public function __construct(
        private readonly string $defaultRecipient,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly int $qrSize = 300,
        private readonly int $qrMargin = 10,
        private readonly int $cacheTtl = 3600
    ) {}

    /**
     * Generate QR code with caching
     */
    public function generateQrCode(?string $recipient = null, ?float $amount = null): OutputResult
    {
        $recipient = $recipient ?? $this->defaultRecipient;
        $cacheKey = sprintf('promptpay_qr_%s_%s', $recipient, $amount ?? 'static');

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($recipient, $amount) {
                $item->expiresAfter($this->cacheTtl);

                $config = QrCodeConfig::create(
                    size: $this->qrSize,
                    margin: $this->qrMargin
                );

                $this->logger->info('Generating PromptPay QR code', [
                    'recipient' => $recipient,
                    'amount' => $amount,
                ]);

                return PromptPay::qrCode($recipient, $amount)
                    ->withConfig($config)
                    ->toDataUri('png');
            });
        } catch (InvalidRecipientException | InvalidAmountException $e) {
            $this->logger->error('PromptPay QR generation failed', [
                'recipient' => $recipient,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        }
    }

    /**
     * Save QR code to filesystem
     */
    public function saveQrCode(string $path, string $recipient, ?float $amount = null): OutputResult
    {
        $config = QrCodeConfig::create(
            size: $this->qrSize,
            margin: $this->qrMargin
        );

        return PromptPay::qrCode($recipient, $amount)
            ->withConfig($config)
            ->toFile($path);
    }

    /**
     * Validate recipient
     */
    public function isValidRecipient(string $recipient): bool
    {
        try {
            PromptPay::generate($recipient)->build();
            return true;
        } catch (InvalidRecipientException $e) {
            return false;
        }
    }

    /**
     * Get default recipient
     */
    public function getDefaultRecipient(): string
    {
        return $this->defaultRecipient;
    }
}

PHP;

echo "\n\n";

// ====================
// Example 3: Service Configuration
// ====================
echo "3. Service Configuration Example:\n";
echo "   File: config/services.yaml\n\n";

echo <<<'YAML'
parameters:
    app.promptpay_recipient: '%env(PROMPTPAY_RECIPIENT)%'
    app.promptpay_qr_size: '%env(int:PROMPTPAY_QR_SIZE)%'
    app.promptpay_qr_margin: '%env(int:PROMPTPAY_QR_MARGIN)%'
    app.promptpay_cache_ttl: '%env(int:PROMPTPAY_CACHE_TTL)%'

services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\Service\PromptPayService:
        arguments:
            $defaultRecipient: '%app.promptpay_recipient%'
            $qrSize: '%app.promptpay_qr_size%'
            $qrMargin: '%app.promptpay_qr_margin%'
            $cacheTtl: '%app.promptpay_cache_ttl%'

YAML;

echo "\n\n";

// ====================
// Example 4: Environment Variables
// ====================
echo "4. Environment Variables (.env):\n\n";

echo <<<'ENV'
###> PromptPay Configuration ###
PROMPTPAY_RECIPIENT=0899999999
PROMPTPAY_QR_SIZE=300
PROMPTPAY_QR_MARGIN=10
PROMPTPAY_CACHE_TTL=3600
###< PromptPay Configuration ###

ENV;

echo "\n\n";

// ====================
// Example 5: Twig Template
// ====================
echo "5. Twig Template Example:\n";
echo "   File: templates/payment/qrcode.html.twig\n\n";

echo <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}PromptPay Payment{% endblock %}

{% block body %}
<div class="container">
    <div class="qr-container">
        <h1>Scan to Pay</h1>

        {% for message in app.flashes('error') %}
            <div class="alert alert-danger">{{ message }}</div>
        {% endfor %}

        <div class="qr-code-wrapper">
            <img src="{{ qr_code }}" alt="PromptPay QR Code" class="qr-image">
        </div>

        <div class="payment-info">
            <p><strong>Amount:</strong> {{ amount|number_format(2) }} THB</p>
            <p><strong>Recipient:</strong> {{ recipient }}</p>
        </div>

        <form action="{{ path('payment_qrcode') }}" method="get" class="update-form">
            <div class="form-group">
                <label for="amount">Update Amount:</label>
                <input type="number" id="amount" name="amount"
                       value="{{ amount }}" step="0.01" min="0.01"
                       class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Update QR Code</button>
        </form>

        <div class="actions">
            <a href="{{ path('payment_download', {recipient: recipient, amount: amount}) }}"
               class="btn btn-success">Download QR Code</a>
        </div>
    </div>
</div>
{% endblock %}

{% block stylesheets %}
<style>
    .qr-container {
        text-align: center;
        margin: 50px auto;
        max-width: 500px;
    }
    .qr-code-wrapper {
        margin: 30px 0;
        padding: 20px;
        background: #f5f5f5;
        border-radius: 8px;
    }
    .qr-image {
        max-width: 300px;
        border: 2px solid #ddd;
        padding: 10px;
        background: white;
    }
    .payment-info {
        margin: 20px 0;
    }
    .update-form {
        margin: 30px 0;
    }
</style>
{% endblock %}

TWIG;

echo "\n\n";

// ====================
// Example 6: Form Type
// ====================
echo "6. Symfony Form Type Example:\n";
echo "   File: src/Form/PaymentFormType.php\n\n";

echo <<<'PHP'
<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;

class PaymentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recipient', TextType::class, [
                'label' => 'Recipient (Phone/Tax ID/E-Wallet)',
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/^[0-9]+$/',
                        'message' => 'Recipient must contain only digits',
                    ]),
                    new Length([
                        'min' => 10,
                        'max' => 15,
                        'minMessage' => 'Recipient must be at least {{ limit }} digits',
                        'maxMessage' => 'Recipient cannot be longer than {{ limit }} digits',
                    ]),
                ],
                'attr' => [
                    'placeholder' => '0899999999',
                    'pattern' => '[0-9]+',
                ],
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Amount (THB)',
                'currency' => 'THB',
                'constraints' => [
                    new NotBlank(),
                    new Positive(),
                ],
                'attr' => [
                    'placeholder' => '100.00',
                    'step' => '0.01',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Generate QR Code',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

PHP;

echo "\n\n";

// ====================
// Example 7: Command
// ====================
echo "7. Symfony Console Command Example:\n";
echo "   File: src/Command/GenerateQrCodeCommand.php\n\n";

echo <<<'PHP'
<?php

namespace App\Command;

use App\Service\PromptPayService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-qrcode',
    description: 'Generate PromptPay QR code',
)]
class GenerateQrCodeCommand extends Command
{
    public function __construct(
        private readonly PromptPayService $promptPayService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('recipient', InputArgument::OPTIONAL, 'Recipient number')
            ->addArgument('amount', InputArgument::OPTIONAL, 'Amount')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $recipient = $input->getArgument('recipient') ?? $this->promptPayService->getDefaultRecipient();
        $amount = $input->getArgument('amount') ? (float) $input->getArgument('amount') : null;
        $outputFile = $input->getOption('output');

        try {
            if ($outputFile) {
                $result = $this->promptPayService->saveQrCode($outputFile, $recipient, $amount);
                $io->success(sprintf('QR code saved to: %s (%d bytes)', $result->getPath(), $result->getSize()));
            } else {
                $result = $this->promptPayService->generateQrCode($recipient, $amount);
                $io->success('QR code generated successfully');
                $io->writeln(sprintf('Size: %d bytes', $result->getSize()));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}

PHP;

echo "\n\n✓ Symfony integration examples completed!\n";
echo "Note: These are conceptual examples. Adapt them to your Symfony application structure.\n";

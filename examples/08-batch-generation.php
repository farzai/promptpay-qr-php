<?php

/**
 * Example 8: Batch Generation
 *
 * This example demonstrates advanced batch processing techniques for generating
 * multiple QR codes efficiently with progress tracking and error handling.
 */

require __DIR__.'/../vendor/autoload.php';

use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;

echo "=== Batch Generation Examples ===\n\n";

// Create output directory
$outputDir = __DIR__.'/output/batch';
if (! is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// ====================
// Example 1: Simple Batch Generation
// ====================
echo "1. Simple Batch Generation:\n";

$recipients = [
    ['id' => 'invoice-001', 'phone' => '0899999999', 'amount' => 100.00],
    ['id' => 'invoice-002', 'phone' => '0888888888', 'amount' => 250.50],
    ['id' => 'invoice-003', 'phone' => '0877777777', 'amount' => 500.00],
    ['id' => 'invoice-004', 'phone' => '0866666666', 'amount' => 1000.00],
    ['id' => 'invoice-005', 'phone' => '0855555555', 'amount' => 150.75],
];

$startTime = microtime(true);
$successCount = 0;
$failCount = 0;

foreach ($recipients as $data) {
    try {
        $filename = "{$outputDir}/{$data['id']}.png";

        $result = PromptPay::qrCode($data['phone'], $data['amount'])
            ->toFile($filename);

        echo "   ✓ Generated: {$data['id']} ({$result->getSize()} bytes)\n";
        $successCount++;
    } catch (\Exception $e) {
        echo "   ✗ Failed: {$data['id']} - {$e->getMessage()}\n";
        $failCount++;
    }
}

$duration = microtime(true) - $startTime;
echo sprintf("\n   Summary: %d succeeded, %d failed in %.2f seconds\n\n", $successCount, $failCount, $duration);

// ====================
// Example 2: Batch Generation from CSV
// ====================
echo "2. Batch Generation from CSV:\n";

// Create sample CSV
$csvFile = $outputDir.'/recipients.csv';
$csvData = [
    ['Invoice ID', 'Recipient', 'Amount'],
    ['INV-2024-001', '0899999999', '1500.00'],
    ['INV-2024-002', '0888888888', '2500.00'],
    ['INV-2024-003', '1234567890123', '3500.00'], // Tax ID
    ['INV-2024-004', '0877777777', '4500.00'],
    ['INV-2024-005', '0866666666', '5500.00'],
];

$fp = fopen($csvFile, 'w');
foreach ($csvData as $row) {
    fputcsv($fp, $row);
}
fclose($fp);

echo "   CSV file created: {$csvFile}\n";
echo "   Processing CSV records...\n\n";

// Process CSV
class CsvBatchProcessor
{
    private int $processed = 0;

    private int $successful = 0;

    private int $failed = 0;

    private array $errors = [];

    public function process(string $csvFile, string $outputDir, ?callable $progressCallback = null): array
    {
        $handle = fopen($csvFile, 'r');
        $headers = fgetcsv($handle); // Skip header row

        while (($row = fgetcsv($handle)) !== false) {
            $this->processed++;

            try {
                [$invoiceId, $recipient, $amount] = $row;
                $filename = "{$outputDir}/{$invoiceId}.png";

                $result = PromptPay::qrCode($recipient, (float) $amount)
                    ->toFile($filename);

                $this->successful++;

                if ($progressCallback) {
                    $progressCallback('success', $invoiceId, $result);
                }
            } catch (\Exception $e) {
                $this->failed++;
                $this->errors[] = [
                    'invoice_id' => $invoiceId ?? 'unknown',
                    'error' => $e->getMessage(),
                ];

                if ($progressCallback) {
                    $progressCallback('error', $invoiceId ?? 'unknown', $e);
                }
            }
        }

        fclose($handle);

        return [
            'processed' => $this->processed,
            'successful' => $this->successful,
            'failed' => $this->failed,
            'errors' => $this->errors,
        ];
    }

    public function getStats(): array
    {
        return [
            'processed' => $this->processed,
            'successful' => $this->successful,
            'failed' => $this->failed,
            'success_rate' => $this->processed > 0 ? ($this->successful / $this->processed) * 100 : 0,
        ];
    }
}

$processor = new CsvBatchProcessor;
$results = $processor->process(
    $csvFile,
    $outputDir,
    function ($status, $id, $data) {
        if ($status === 'success') {
            echo "   ✓ {$id}: {$data->getSize()} bytes\n";
        } else {
            echo "   ✗ {$id}: {$data->getMessage()}\n";
        }
    }
);

echo "\n   Batch Summary:\n";
echo "   - Total processed: {$results['processed']}\n";
echo "   - Successful: {$results['successful']}\n";
echo "   - Failed: {$results['failed']}\n";

if (! empty($results['errors'])) {
    echo "\n   Errors:\n";
    foreach ($results['errors'] as $error) {
        echo "   - {$error['invoice_id']}: {$error['error']}\n";
    }
}

echo "\n";

// ====================
// Example 3: Batch Generation with JSON Configuration
// ====================
echo "3. Batch Generation from JSON Configuration:\n";

$jsonConfig = [
    'default_config' => [
        'size' => 400,
        'margin' => 15,
    ],
    'recipients' => [
        [
            'name' => 'Store A',
            'recipient' => '0899999999',
            'amount' => 1000.00,
            'config' => ['size' => 500, 'margin' => 20], // Custom config
        ],
        [
            'name' => 'Store B',
            'recipient' => '0888888888',
            'amount' => 2000.00,
        ],
        [
            'name' => 'Store C - Tax ID',
            'recipient' => '1234567890123',
            'amount' => 3000.00,
        ],
    ],
];

// Save JSON config
$jsonFile = $outputDir.'/batch-config.json';
file_put_contents($jsonFile, json_encode($jsonConfig, JSON_PRETTY_PRINT));

echo "   JSON configuration created: {$jsonFile}\n";
echo "   Processing JSON configuration...\n\n";

class JsonBatchProcessor
{
    public function process(string $jsonFile, string $outputDir): array
    {
        $config = json_decode(file_get_contents($jsonFile), true);
        $defaultConfig = $config['default_config'];
        $results = [];

        foreach ($config['recipients'] as $index => $item) {
            $name = $item['name'];
            $recipient = $item['recipient'];
            $amount = $item['amount'];

            // Use custom config or default
            $qrConfig = isset($item['config'])
                ? QrCodeConfig::create($item['config']['size'], $item['config']['margin'])
                : QrCodeConfig::create($defaultConfig['size'], $defaultConfig['margin']);

            try {
                $filename = $outputDir.'/json-'.($index + 1).'-'.preg_replace('/[^a-z0-9]/i', '-', $name).'.png';

                $result = PromptPay::qrCode($recipient, $amount)
                    ->withConfig($qrConfig)
                    ->toFile($filename);

                $results[] = [
                    'name' => $name,
                    'status' => 'success',
                    'file' => basename($filename),
                    'size' => $result->getSize(),
                ];

                echo "   ✓ {$name}: {$result->getSize()} bytes (config: {$qrConfig->getSize()}x{$qrConfig->getSize()})\n";
            } catch (\Exception $e) {
                $results[] = [
                    'name' => $name,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];

                echo "   ✗ {$name}: {$e->getMessage()}\n";
            }
        }

        return $results;
    }
}

$jsonProcessor = new JsonBatchProcessor;
$jsonResults = $jsonProcessor->process($jsonFile, $outputDir);

echo "\n";

// ====================
// Example 4: Memory-Efficient Large Batch Processing
// ====================
echo "4. Memory-Efficient Large Batch Processing:\n";

class MemoryEfficientBatchProcessor
{
    private int $batchSize;

    private string $outputDir;

    public function __construct(string $outputDir, int $batchSize = 100)
    {
        $this->outputDir = $outputDir;
        $this->batchSize = $batchSize;
    }

    public function processGenerator(iterable $dataSource): \Generator
    {
        $batch = [];
        $batchNumber = 1;

        foreach ($dataSource as $item) {
            $batch[] = $item;

            if (count($batch) >= $this->batchSize) {
                yield $this->processBatch($batch, $batchNumber);
                $batch = [];
                $batchNumber++;

                // Force garbage collection for large batches
                gc_collect_cycles();
            }
        }

        // Process remaining items
        if (! empty($batch)) {
            yield $this->processBatch($batch, $batchNumber);
        }
    }

    private function processBatch(array $batch, int $batchNumber): array
    {
        $results = [
            'batch_number' => $batchNumber,
            'size' => count($batch),
            'successful' => 0,
            'failed' => 0,
            'memory_used' => 0,
        ];

        foreach ($batch as $index => $item) {
            try {
                $filename = sprintf(
                    '%s/batch-%03d-item-%03d.png',
                    $this->outputDir,
                    $batchNumber,
                    $index + 1
                );

                PromptPay::qrCode($item['recipient'], $item['amount'])
                    ->toFile($filename);

                $results['successful']++;
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        $results['memory_used'] = memory_get_usage(true);

        return $results;
    }
}

// Simulate large dataset using generator
function getLargeDataset(int $count): \Generator
{
    for ($i = 1; $i <= $count; $i++) {
        yield [
            'id' => sprintf('BULK-%05d', $i),
            'recipient' => '0899999999',
            'amount' => rand(100, 10000) / 100, // Random amount between 1.00 and 100.00
        ];
    }
}

echo "   Processing 250 QR codes in batches of 50...\n";

$memoryProcessor = new MemoryEfficientBatchProcessor($outputDir, 50);
$totalSuccess = 0;
$totalFailed = 0;

foreach ($memoryProcessor->processGenerator(getLargeDataset(250)) as $batchResult) {
    $totalSuccess += $batchResult['successful'];
    $totalFailed += $batchResult['failed'];

    $memoryMB = round($batchResult['memory_used'] / 1024 / 1024, 2);
    echo sprintf(
        "   Batch %d: %d items, %d successful, %d failed, Memory: %s MB\n",
        $batchResult['batch_number'],
        $batchResult['size'],
        $batchResult['successful'],
        $batchResult['failed'],
        $memoryMB
    );
}

echo "\n   Total: {$totalSuccess} successful, {$totalFailed} failed\n\n";

// ====================
// Example 5: Parallel Batch Processing (Conceptual)
// ====================
echo "5. Parallel Batch Processing Strategy:\n";
echo "   Note: This is a conceptual example showing how you could structure parallel processing.\n\n";

echo <<<'PHP'
// Using Symfony Process component or similar
use Symfony\Component\Process\Process;

class ParallelBatchProcessor
{
    private int $maxParallel = 4;

    public function process(array $batches): void
    {
        $processes = [];
        $completed = 0;
        $total = count($batches);

        foreach ($batches as $index => $batch) {
            // Create process for each batch
            $process = new Process([
                'php',
                'process-batch.php',
                json_encode($batch),
            ]);

            $process->start();
            $processes[$index] = $process;

            // Wait if we've reached max parallel processes
            if (count($processes) >= $this->maxParallel) {
                $this->waitForSlot($processes);
            }
        }

        // Wait for all remaining processes
        foreach ($processes as $process) {
            $process->wait();
        }
    }

    private function waitForSlot(array &$processes): void
    {
        while (count($processes) >= $this->maxParallel) {
            foreach ($processes as $index => $process) {
                if (!$process->isRunning()) {
                    unset($processes[$index]);
                }
            }
            usleep(100000); // 100ms
        }
    }
}

PHP;

echo "\n";

// ====================
// Example 6: Progress Tracking with Event System
// ====================
echo "6. Progress Tracking with Event System:\n";

interface BatchEventListener
{
    public function onBatchStart(int $total): void;

    public function onItemProcessed(string $id, bool $success): void;

    public function onBatchComplete(array $stats): void;
}

class ConsoleProgressListener implements BatchEventListener
{
    private int $total = 0;

    private int $current = 0;

    public function onBatchStart(int $total): void
    {
        $this->total = $total;
        echo "   Starting batch processing: {$total} items\n";
        echo '   Progress: [';
    }

    public function onItemProcessed(string $id, bool $success): void
    {
        $this->current++;
        $percentage = ($this->current / $this->total) * 100;

        if ($this->current % 5 === 0 || $this->current === $this->total) {
            $filled = (int) ($percentage / 10);
            echo str_repeat('=', $filled);
        }
    }

    public function onBatchComplete(array $stats): void
    {
        echo "] 100%\n";
        echo sprintf(
            "   Completed: %d successful, %d failed, %.2f seconds\n",
            $stats['successful'],
            $stats['failed'],
            $stats['duration']
        );
    }
}

class BatchProcessorWithEvents
{
    private array $listeners = [];

    public function addEventListener(BatchEventListener $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function process(array $items, string $outputDir): array
    {
        $startTime = microtime(true);
        $stats = ['successful' => 0, 'failed' => 0, 'duration' => 0];

        foreach ($this->listeners as $listener) {
            $listener->onBatchStart(count($items));
        }

        foreach ($items as $item) {
            try {
                $filename = "{$outputDir}/event-{$item['id']}.png";
                PromptPay::qrCode($item['recipient'], $item['amount'])->toFile($filename);
                $stats['successful']++;
                $success = true;
            } catch (\Exception $e) {
                $stats['failed']++;
                $success = false;
            }

            foreach ($this->listeners as $listener) {
                $listener->onItemProcessed($item['id'], $success);
            }
        }

        $stats['duration'] = microtime(true) - $startTime;

        foreach ($this->listeners as $listener) {
            $listener->onBatchComplete($stats);
        }

        return $stats;
    }
}

// Example usage
$eventProcessor = new BatchProcessorWithEvents;
$eventProcessor->addEventListener(new ConsoleProgressListener);

$testItems = array_map(
    fn ($i) => ['id' => "EVENT-{$i}", 'recipient' => '0899999999', 'amount' => 100.00],
    range(1, 25)
);

$eventProcessor->process($testItems, $outputDir);

echo "\n✓ All batch generation examples completed!\n";
echo "Output directory: {$outputDir}\n";
echo "Note: These patterns can be adapted for large-scale QR code generation needs.\n";

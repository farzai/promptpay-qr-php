# PromptPay QR Code Generator - PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/farzai/promptpay.svg?style=flat-square)](https://packagist.org/packages/farzai/promptpay)
[![Tests](https://img.shields.io/github/actions/workflow/status/farzai/promptpay-qr-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/farzai/promptpay-qr-php/actions/workflows/run-tests.yml)
[![codecov](https://codecov.io/gh/farzai/promptpay-qr-php/branch/main/graph/badge.svg)](https://codecov.io/gh/farzai/promptpay-qr-php)
[![Total Downloads](https://img.shields.io/packagist/dt/farzai/promptpay.svg?style=flat-square)](https://packagist.org/packages/farzai/promptpay)

A modern, type-safe PHP library for generating PromptPay QR codes.

## Features

- **Zero Config** - Works out of the box with sensible defaults
- **Multiple Formats** - PNG, SVG, GIF support
- **Amount Support** - Static or dynamic QR codes
- **CLI Tool** - Command-line interface included

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

### For PHP Applications

```bash
composer require farzai/promptpay
```

### For CLI Usage (Global)

```bash
composer global require farzai/promptpay
```

Make sure Composer's global bin directory is in your `$PATH`:
- **macOS/Linux**: `~/.composer/vendor/bin` or `~/.config/composer/vendor/bin`
- **Windows**: `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`

## Quick Start

### Simple Example

```php
use Farzai\PromptPay\PromptPay;

// Generate QR code (backward compatible)
$qrCode = PromptPay::create('0899999999', 100);
echo $qrCode; // Raw payload string
```

### Modern Builder API (Recommended)

```php
use Farzai\PromptPay\PromptPay;

// Immutable builder pattern
$result = PromptPay::generate('0899999999')
    ->withAmount(100.50)
    ->toDataUri('png');

echo '<img src="' . $result->getData() . '" />';
```

## Usage Guide

### Creating QR Codes

#### Static QR Code (No Amount)

```php
// Customer scans and enters amount themselves
$qrCode = PromptPay::generate('0899999999')->build();
```

#### Dynamic QR Code (With Amount)

```php
// Amount is pre-filled in payment app
$result = PromptPay::qrCode('0899999999', 150.75)
    ->toDataUri('png');
```

### Recipient Types

The library automatically detects recipient type based on length:

```php
// Phone Number (10 digits)
PromptPay::generate('0899999999');

// Tax ID / Citizen ID (13 digits)
PromptPay::generate('1234567890123');

// E-Wallet ID (15 digits)
PromptPay::generate('123456789012345');

// Special characters are automatically removed
PromptPay::generate('089-999-9999'); // Works!
```

### Output Formats

#### 1. Data URI (for `<img>` tags)

```php
$result = PromptPay::generate('0899999999')
    ->withAmount(100)
    ->toDataUri('png');

echo '<img src="' . $result->getData() . '" />';

// Available formats: png, svg, pdf, gif
```

#### 2. Save to File

```php
$result = PromptPay::qrCode('0899999999', 100)
    ->toFile('qrcode.png');

echo 'Saved to: ' . $result->getPath();
echo 'File size: ' . $result->getSize() . ' bytes';
```

#### 3. PSR-7 HTTP Response

First, install any PSR-17/PSR-7 implementation:

```bash
# Choose one:
composer require nyholm/psr7
# or
composer require guzzlehttp/psr7
```

Then create the response:

```php
use Nyholm\Psr7\Factory\Psr17Factory;

// Create PSR-17 factory (implements both ResponseFactory and StreamFactory)
$factory = new Psr17Factory();

$response = PromptPay::generate('0899999999')
    ->withAmount(100)
    ->toResponse($factory, $factory);

// Returns PSR-7 ResponseInterface
// Perfect for Laravel, Symfony, Slim, etc.
return $response;
```

**With Guzzle PSR-7:**

```php
use GuzzleHttp\Psr7\HttpFactory;

$factory = new HttpFactory();
$response = PromptPay::generate('0899999999')
    ->withAmount(100)
    ->toResponse($factory, $factory);
```

**Why PSR-17?** No hard dependencies! Works with ANY PSR-7 library - choose the one your project already uses.

#### 4. Console Output (CLI)

```php
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();
PromptPay::generate('0899999999')
    ->withAmount(100)
    ->toConsole($output);
```

#### 5. Raw Payload String

```php
$payload = PromptPay::generate('0899999999')
    ->withAmount(100)
    ->toPayload();

echo $payload;
// 00020101021229370016A000000677010111011300668999999995802TH53037645406100.006304CB89
```

### Advanced Configuration

```php
use Farzai\PromptPay\PromptPay;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;

// Custom QR code size and margin
$config = QrCodeConfig::create(
    size: 400,      // 400x400 pixels
    margin: 20,     // 20px margin
    encoding: 'UTF-8'
);

$result = PromptPay::generate('0899999999')
    ->withAmount(100)
    ->withConfig($config)
    ->toDataUri('svg');
```

### Immutable Builder Pattern

The builder is fully immutable - each method returns a new instance:

```php
$builder1 = PromptPay::generate('0899999999')->withAmount(100);
$builder2 = $builder1->withAmount(200); // New instance!

echo $builder1->getAmount(); // 100
echo $builder2->getAmount(); // 200
```

## Validation & Error Handling

The library provides comprehensive validation with helpful error messages:

### Recipient Validation

```php
use Farzai\PromptPay\Exceptions\InvalidRecipientException;

try {
    PromptPay::generate('12345')->build(); // Too short
} catch (InvalidRecipientException $e) {
    echo $e->getMessage();
    // "Invalid recipient length: 5 digits. Expected formats:
    // Too short! • Phone Number: 10 digits (e.g., 0899999999)
    // • Tax ID: 13 digits (e.g., 1234567890123)
    // • E-Wallet ID: 15 digits (e.g., 123456789012345)"

    echo $e->getCode(); // 1003
}
```

### Amount Validation

```php
use Farzai\PromptPay\Exceptions\InvalidAmountException;

try {
    PromptPay::qrCode('0899999999', -50)->build();
} catch (InvalidAmountException $e) {
    echo $e->getMessage();
    // "Invalid amount: -50.00 THB cannot be negative.
    // Please provide a positive amount."

    echo $e->getCode(); // 2002
}
```

### Error Codes Reference

**Recipient Errors (1xxx)**
- `1001` - Empty recipient
- `1002` - Not numeric
- `1003` - Invalid length
- `1004` - Empty after normalization

**Amount Errors (2xxx)**
- `2001` - Not numeric
- `2002` - Negative amount
- `2003` - Too large (> 999,999,999.99)
- `2004` - Zero (when positive required)
- `2005` - Too small (< 0.01)

**Configuration Errors (3xxx)**
- `3001` - Size too small
- `3002` - Size too large
- `3003` - Margin too small
- `3004` - Margin too large
- `3005` - Invalid encoding
- `3006` - Invalid path
- `3007` - Missing dependency

## CLI Usage

```bash
# Basic usage
promptpay 0899999999 100

# Interactive mode (no arguments)
promptpay

# Output shows QR code in terminal
```

## Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer analyse

# Run code formatting
composer format
```

## Examples

Check the `examples/` directory for real-world usage scenarios:

- **01-basic-usage.php** - Basic QR code generation and builder patterns
- **02-file-generation.php** - Saving to files with custom configurations
- **03-error-handling.php** - Comprehensive error handling patterns
- **04-web-integration.php** - Web form integration with HTML
- **05-laravel-integration.php** - Laravel framework integration
- **06-symfony-integration.php** - Symfony framework integration
- **07-custom-validation.php** - Custom validation patterns and business rules
- **08-batch-generation.php** - Batch processing and bulk generation

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security vulnerabilities, please review [our security policy](../../security/policy) on how to report them.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.

## Credits

- [parsilver](https://github.com/parsilver)
- [All Contributors](../../contributors)

## Acknowledgments

- Built with [endroid/qr-code](https://github.com/endroid/qr-code)
- Follows PromptPay EMV QR Code Specification
- Inspired by Thailand's National e-Payment Master Plan

---

**Made with ❤️ for the Thai developer community**

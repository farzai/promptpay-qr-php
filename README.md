# PromptPay QR Code Generator - PHP


![Example](assets/example.png)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/farzai/promptpay.svg?style=flat-square)](https://packagist.org/packages/farzai/promptpay)
[![Tests](https://img.shields.io/github/actions/workflow/status/farzai/promptpay-qr-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/farzai/promptpay-qr-php/actions/workflows/run-tests.yml)
[![codecov](https://codecov.io/gh/farzai/promptpay-qr-php/branch/main/graph/badge.svg)](https://codecov.io/gh/farzai/promptpay-qr-php)
[![Total Downloads](https://img.shields.io/packagist/dt/farzai/promptpay.svg?style=flat-square)](https://packagist.org/packages/farzai/promptpay)


This library that allows you to create PromptPay QR codes. 
You can generate a QR code for receiving payments through PromptPay, which is a popular payment method in Thailand.


## Requirements

- PHP 8.0 or higher

## Installation (For CLI)

You can install the package globally via composer:

```bash
composer global require farzai/promptpay
```
Make sure to place Composer's system-wide vendor bin directory in your $PATH so the promptpay executable can be located by your system. 
This directory exists in different locations based on your operating system;
however, some common locations include:


For macOS
```
$HOME/.composer/vendor/bin
```

For GNU / Linux Distributions
```
GNU / Linux Distributions: $HOME/.config/composer/vendor/bin
```

For Windows
```
%USERPROFILE%\AppData\Roaming\Composer\vendor\bin
```

## Usage

```bash
$ promptpay <phone-number> <amount>
```

For example, to generate a QR code for receiving 100 THB from the phone number 0988888888:
```bash
$ promptpay 0988888888 100
```


---


## Installation (For PHP Application)


You can install the package via composer:

```bash
composer require farzai/promptpay
```

## Usage

For example, to generate a QR code for receiving 100 THB from the phone number 0988888888:

```php
use Farzai\PromptPay\PromptPay;

$qrCode = PromptPay::create('0988888888', 100);

$imageUri = $qrCode->toDataUri('png');

echo '<img src="' . $imageUri . '" />';
```

Or you can save the QR code to a file:

```php
use Farzai\PromptPay\PromptPay;

$qrCode = PromptPay::create('0988888888', 100):

$imagePath = $qrCode->toFile('qr-code.png');

echo 'QR code saved to ' . $imagePath;
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/farzai/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [parsilver](https://github.com/parsilver)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

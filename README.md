# PromptPay QR Code Generator - PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/farzai/promptpay.svg?style=flat-square)](https://packagist.org/packages/farzai/promptpay)
[![Tests](https://img.shields.io/github/actions/workflow/status/farzai/promptpay/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/farzai/promptpay/actions/workflows/run-tests.yml)
[![codecov](https://codecov.io/gh/farzai/promptpay-qr-php/branch/main/graph/badge.svg)](https://codecov.io/gh/farzai/promptpay-qr-php)
[![Total Downloads](https://img.shields.io/packagist/dt/farzai/promptpay.svg?style=flat-square)](https://packagist.org/packages/farzai/promptpay)

This is where your description should go. Try and limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require farzai/promptpay
```

## Usage

```php
$generator = new Farzai\PromptPay\Generator();

$qrCode = $generator->generate(
    target: "0812345678", 
    amount: 100
);

// Next, you can save the image to a file:
$qrCode->save('qrcode.png');

// Or insert it directly into a template:
echo '<img src="' . $qrCode->asDataUri() . '" />';
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [parsilver](https://github.com/parsilver)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

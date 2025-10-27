<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Enums;

/**
 * PromptPay QR Code field identifiers based on EMV QR Code specification
 */
enum PromptPayField: string
{
    case PAYLOAD_FORMAT = '00';
    case POI_METHOD = '01';
    case MERCHANT_INFORMATION_BOT = '29';
    case TRANSACTION_CURRENCY = '53';
    case TRANSACTION_AMOUNT = '54';
    case COUNTRY_CODE = '58';
    case CRC = '63';
}

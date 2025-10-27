<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Enums;

/**
 * Merchant information template field identifiers
 */
enum MerchantInfoField: string
{
    case TEMPLATE_ID_GUID = '00';
    case PHONE_NUMBER = '01';
    case TAX_ID = '02';
    case EWALLET_ID = '03';
}

<?php

declare(strict_types=1);

namespace Farzai\PromptPay\ValueObjects;

/**
 * Recipient type enum
 */
enum RecipientType: string
{
    case PHONE = '01';
    case TAX_ID = '02';
    case EWALLET = '03';
}

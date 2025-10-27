<?php

declare(strict_types=1);

namespace Farzai\PromptPay\Enums;

/**
 * Point of Initiation Method
 */
enum PoiMethod: string
{
    case STATIC = '11';  // For QR codes without amount
    case DYNAMIC = '12'; // For QR codes with amount
}

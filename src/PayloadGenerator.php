<?php

declare(strict_types=1);

namespace Farzai\PromptPay;

use Farzai\PromptPay\Contracts\CrcAlgorithm as AlgorithmContract;
use Farzai\PromptPay\Contracts\PayloadGenerator as Contract;
use Farzai\PromptPay\Enums\MerchantInfoField;
use Farzai\PromptPay\Enums\PoiMethod;
use Farzai\PromptPay\Enums\PromptPayConstants;
use Farzai\PromptPay\Enums\PromptPayField;
use Farzai\PromptPay\ValueObjects\Amount;
use Farzai\PromptPay\ValueObjects\Recipient;
use Farzai\PromptPay\ValueObjects\RecipientType;

class PayloadGenerator implements Contract
{
    public function __construct(
        private readonly AlgorithmContract $algorithm
    ) {}

    /**
     * Generate payload
     *
     * @param  int|float|null  $amount
     */
    public function generate(string $target, $amount = null): string
    {
        $recipient = Recipient::fromString($target);
        $amountObj = Amount::fromNumeric($amount);

        $payload = $this->createPayload($recipient, $amountObj);

        return $this->concat($payload);
    }

    /**
     * @return array<string>
     */
    private function createPayload(Recipient $recipient, ?Amount $amount): array
    {
        // Determine POI method based on amount presence
        $poiMethod = $amount?->isPresent()
            ? PoiMethod::DYNAMIC
            : PoiMethod::STATIC;

        // Create the payload of the QR code
        $data = [
            // Payload format indicator
            $this->formatIdValue(
                PromptPayField::PAYLOAD_FORMAT,
                PromptPayConstants::PAYLOAD_FORMAT_EMV_QRCPS
            ),

            // Point of initiation method
            $this->formatIdValue(
                PromptPayField::POI_METHOD,
                $poiMethod->value
            ),

            // Merchant information
            $this->formatIdValue(
                PromptPayField::MERCHANT_INFORMATION_BOT,
                $this->concat([
                    $this->formatIdValue(
                        MerchantInfoField::TEMPLATE_ID_GUID,
                        PromptPayConstants::GUID_PROMPTPAY
                    ),
                    $this->formatIdValue(
                        $recipient->getType(),
                        $recipient->getFormattedValue()
                    ),
                ]),
            ),

            // Country code
            $this->formatIdValue(
                PromptPayField::COUNTRY_CODE,
                PromptPayConstants::COUNTRY_CODE_TH
            ),

            // Transaction currency
            $this->formatIdValue(
                PromptPayField::TRANSACTION_CURRENCY,
                PromptPayConstants::TRANSACTION_CURRENCY_THB
            ),
        ];

        // Add transaction amount if available
        if ($amount !== null) {
            $data[] = $this->formatIdValue(
                PromptPayField::TRANSACTION_AMOUNT,
                $amount->getFormatted()
            );
        }

        // Add CRC checksum
        $crc = $this->algorithm->generate(
            data: $this->concat($data).PromptPayField::CRC->value.'04'
        );

        $data[] = $this->formatIdValue(PromptPayField::CRC, $crc);

        return $data;
    }

    private function formatIdValue(PromptPayField|MerchantInfoField|RecipientType $id, string $value): string
    {
        $paddedLength = str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT);

        return $id->value.$paddedLength.$value;
    }

    /**
     * Concat array data
     *
     * @param  array<string>  $data
     */
    private function concat(array $data): string
    {
        return implode('', $data);
    }
}

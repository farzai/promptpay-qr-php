<?php

namespace Farzai\PromptPay;

use Farzai\PromptPay\Contracts\CrcAlgorithm as AlgorithmContract;
use Farzai\PromptPay\Contracts\PayloadGenerator as Contract;

class PayloadGenerator implements Contract
{
    const ID_PAYLOAD_FORMAT = '00';

    const ID_POI_METHOD = '01';

    const ID_MERCHANT_INFORMATION_BOT = '29';

    const ID_TRANSACTION_CURRENCY = '53';

    const ID_TRANSACTION_AMOUNT = '54';

    const ID_COUNTRY_CODE = '58';

    const ID_CRC = '63';

    const PAYLOAD_FORMAT_EMV_QRCPS_MERCHANT_PRESENTED_MODE = '01';

    const POI_METHOD_STATIC = '11';

    const POI_METHOD_DYNAMIC = '12';

    const MERCHANT_INFORMATION_TEMPLATE_ID_GUID = '00';

    const BOT_ID_MERCHANT_PHONE_NUMBER = '01';

    const BOT_ID_MERCHANT_TAX_ID = '02';

    const BOT_ID_MERCHANT_EWALLET_ID = '03';

    const GUID_PROMPTPAY = 'A000000677010111';

    const TRANSACTION_CURRENCY_THB = '764';

    const COUNTRY_CODE_TH = 'TH';

    private AlgorithmContract $algorithm;

    public function __construct(AlgorithmContract $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Generate payload
     *
     * @param  int|float  $amount
     */
    public function generate(string $target, $amount = null): string
    {
        $payload = $this->createPayload(
            target: $this->normalizeTarget($target),
            amount: $this->normalizeAmount($amount),
        );

        return $this->concat($payload);
    }

    /**
     * @param  int|float  $amount
     */
    private function createPayload(string $target, $amount = null): array
    {
        // Determine the type of target: phone number, tax ID or e-wallet ID
        if (strlen($target) >= 15) {
            $type = self::BOT_ID_MERCHANT_EWALLET_ID;
        } elseif (strlen($target) >= 13) {
            $type = self::BOT_ID_MERCHANT_TAX_ID;
        } else {
            $type = self::BOT_ID_MERCHANT_PHONE_NUMBER;
        }

        // Create the payload of the QR code
        $data = [
            // Payload format indicator
            $this->formatIdValue(
                self::ID_PAYLOAD_FORMAT,
                self::PAYLOAD_FORMAT_EMV_QRCPS_MERCHANT_PRESENTED_MODE
            ),

            // Point of initiation method
            $this->formatIdValue(
                self::ID_POI_METHOD,
                $amount
                    ? self::POI_METHOD_DYNAMIC
                    : self::POI_METHOD_STATIC
            ),

            // Merchant information
            $this->formatIdValue(
                self::ID_MERCHANT_INFORMATION_BOT,
                $this->concat([
                    $this->formatIdValue(self::MERCHANT_INFORMATION_TEMPLATE_ID_GUID, self::GUID_PROMPTPAY),
                    $this->formatIdValue($type, $this->toTargetValue($target)),
                ]),
            ),

            // Country code
            $this->formatIdValue(self::ID_COUNTRY_CODE, self::COUNTRY_CODE_TH),

            // Transaction currency
            $this->formatIdValue(self::ID_TRANSACTION_CURRENCY, self::TRANSACTION_CURRENCY_THB),
        ];

        // Add transaction amount if available
        if (! is_null($amount)) {
            array_push($data, $this->formatIdValue(
                self::ID_TRANSACTION_AMOUNT,
                $this->standardizeDecimalFormat($amount)
            ));
        }

        // Add CRC checksum
        $crc = $this->algorithm->generate(
            data: $this->concat($data).self::ID_CRC.'04'
        );

        array_push($data, $this->formatIdValue(self::ID_CRC, $crc));

        return $data;
    }

    private function normalizeTarget(string $target): string
    {
        return preg_replace('/[^0-9]/', '', $target);
    }

    /**
     * @param  int|float|null  $amount
     * @return int|float|null
     */
    private function normalizeAmount($amount)
    {
        if (is_null($amount) || ! is_numeric($amount)) {
            return null;
        }

        return $amount;
    }

    /**
     * @return string
     */
    private function formatIdValue(string $id, string $value)
    {
        $paddedLength = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);

        return $id.$paddedLength.$value;
    }

    private function toTargetValue(string $target): string
    {
        if (strlen($target) >= 13) {
            return $target;
        }

        $target = preg_replace('/^0/', '66', $target);

        // pad 0 to 13 digits
        return str_pad($target, 13, '0', STR_PAD_LEFT);
    }

    /**
     * Convert amount to standard decimal format
     *
     * @param  int|float  $amount
     * @return string
     */
    private function standardizeDecimalFormat($amount)
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * Concat array data
     */
    private function concat(array $data): string
    {
        return implode('', $data);
    }
}

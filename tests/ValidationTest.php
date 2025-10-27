<?php

use Farzai\PromptPay\Exceptions\InvalidAmountException;
use Farzai\PromptPay\Exceptions\InvalidRecipientException;
use Farzai\PromptPay\Validators\AmountValidator;
use Farzai\PromptPay\Validators\RecipientValidator;
use Farzai\PromptPay\ValueObjects\Amount;
use Farzai\PromptPay\ValueObjects\Recipient;

describe('RecipientValidator', function () {
    it('validates phone number successfully', function () {
        expect(fn () => RecipientValidator::validate('0899999999'))->not->toThrow(InvalidRecipientException::class);
    });

    it('validates phone number with dashes', function () {
        expect(fn () => RecipientValidator::validate('089-999-9999'))->not->toThrow(InvalidRecipientException::class);
    });

    it('validates tax ID successfully', function () {
        expect(fn () => RecipientValidator::validate('1234567890123'))->not->toThrow(InvalidRecipientException::class);
    });

    it('validates e-wallet ID successfully', function () {
        expect(fn () => RecipientValidator::validate('123456789012345'))->not->toThrow(InvalidRecipientException::class);
    });

    it('throws exception for empty recipient', function () {
        RecipientValidator::validate('');
    })->throws(InvalidRecipientException::class, 'Recipient cannot be empty');

    it('throws exception for too short recipient', function () {
        RecipientValidator::validate('12345');
    })->throws(InvalidRecipientException::class);

    it('throws exception for too long recipient', function () {
        RecipientValidator::validate('1234567890123456');
    })->throws(InvalidRecipientException::class);

    it('formats phone number for display', function () {
        $formatted = RecipientValidator::formatForDisplay('0899999999');
        expect($formatted)->toBe('089-999-9999');
    });

    it('normalizes recipient by removing special characters', function () {
        $normalized = RecipientValidator::normalize('089-999-9999');
        expect($normalized)->toBe('0899999999');
    });
});

describe('AmountValidator', function () {
    it('validates positive amount successfully', function () {
        expect(fn () => AmountValidator::validate(100))->not->toThrow(InvalidAmountException::class);
        expect(fn () => AmountValidator::validate(99.99))->not->toThrow(InvalidAmountException::class);
    });

    it('validates zero amount', function () {
        expect(fn () => AmountValidator::validate(0))->not->toThrow(InvalidAmountException::class);
    });

    it('throws exception for negative amount', function () {
        AmountValidator::validate(-10);
    })->throws(InvalidAmountException::class, 'cannot be negative');

    it('throws exception for amount too large', function () {
        AmountValidator::validate(9999999999.99);
    })->throws(InvalidAmountException::class, 'exceeds maximum');

    it('validates positive amount (non-zero)', function () {
        expect(fn () => AmountValidator::validatePositive(0.01))->not->toThrow(InvalidAmountException::class);
    });

    it('throws exception for zero in validatePositive', function () {
        AmountValidator::validatePositive(0);
    })->throws(InvalidAmountException::class);

    it('formats amount for display with thousands separator', function () {
        $formatted = AmountValidator::formatForDisplay(1234567.89);
        expect($formatted)->toBe('1,234,567.89');
    });
});

describe('Recipient Value Object with Validator', function () {
    it('creates recipient with valid phone number', function () {
        $recipient = Recipient::fromString('0899999999');
        expect($recipient->getValue())->toBe('0899999999');
    });

    it('creates recipient with formatted input', function () {
        $recipient = Recipient::fromString('089-999-9999');
        expect($recipient->getValue())->toBe('0899999999');
    });

    it('throws exception with helpful message for invalid recipient', function () {
        Recipient::fromString('abc');
    })->throws(InvalidRecipientException::class);

    it('provides display value for phone number', function () {
        $recipient = Recipient::fromString('0899999999');
        expect($recipient->getDisplayValue())->toBe('089-999-9999');
    });
});

describe('Amount Value Object with Validator', function () {
    it('creates amount with valid value', function () {
        $amount = Amount::from(100.50);
        expect($amount->getValue())->toBe(100.50);
    });

    it('throws exception for negative amount', function () {
        Amount::from(-10);
    })->throws(InvalidAmountException::class, 'cannot be negative');

    it('throws exception for too large amount', function () {
        Amount::from(9999999999.99);
    })->throws(InvalidAmountException::class, 'exceeds maximum');

    it('creates positive amount', function () {
        $amount = Amount::positive(50);
        expect($amount->getValue())->toBe(50);
    });

    it('throws exception for zero in positive()', function () {
        Amount::positive(0);
    })->throws(InvalidAmountException::class);

    it('provides formatted display value', function () {
        $amount = Amount::from(12345.67);
        expect($amount->getDisplayValue())->toBe('12,345.67');
    });

    it('fromNumeric still returns null for null', function () {
        $amount = Amount::fromNumeric(null);
        expect($amount)->toBeNull();
    });
});

describe('Error Codes', function () {
    it('has error code for empty recipient', function () {
        try {
            Recipient::fromString('');
        } catch (InvalidRecipientException $e) {
            expect($e->getCode())->toBe(InvalidRecipientException::CODE_EMPTY);
        }
    });

    it('has error code for negative amount', function () {
        try {
            Amount::from(-10);
        } catch (InvalidAmountException $e) {
            expect($e->getCode())->toBe(InvalidAmountException::CODE_NEGATIVE);
        }
    });

    it('has error code for amount too large', function () {
        try {
            Amount::from(9999999999.99);
        } catch (InvalidAmountException $e) {
            expect($e->getCode())->toBe(InvalidAmountException::CODE_TOO_LARGE);
        }
    });
});

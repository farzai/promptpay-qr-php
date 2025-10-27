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

    it('determines type as Phone Number for 10 digits', function () {
        $type = RecipientValidator::determineType('0899999999');
        expect($type)->toBe('Phone Number');
    });

    it('determines type as Tax ID for 13 digits', function () {
        $type = RecipientValidator::determineType('1234567890123');
        expect($type)->toBe('Tax ID');
    });

    it('determines type as E-Wallet ID for 15 digits', function () {
        $type = RecipientValidator::determineType('123456789012345');
        expect($type)->toBe('E-Wallet ID');
    });

    it('formats Tax ID for display', function () {
        $formatted = RecipientValidator::formatForDisplay('1234567890123');
        expect($formatted)->toBe('1-2345-67890-12-3');
    });

    it('returns normalized value for non-standard length', function () {
        $formatted = RecipientValidator::formatForDisplay('12345678901234');
        expect($formatted)->toBe('12345678901234');
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

    it('checks if amount is within recommended range', function () {
        expect(AmountValidator::isRecommendedRange(50000))->toBeTrue();
        expect(AmountValidator::isRecommendedRange(0.01))->toBeTrue();
        expect(AmountValidator::isRecommendedRange(1000000))->toBeTrue();
    });

    it('returns false for amount above recommended range', function () {
        expect(AmountValidator::isRecommendedRange(2000000))->toBeFalse();
    });

    it('returns false for amount below minimum', function () {
        expect(AmountValidator::isRecommendedRange(0))->toBeFalse();
    });

    it('gets recommended maximum amount', function () {
        expect(AmountValidator::getRecommendedMax())->toBe(1000000.00);
    });

    it('gets absolute maximum amount', function () {
        expect(AmountValidator::getAbsoluteMax())->toBe(999999999.99);
    });

    it('gets minimum amount', function () {
        expect(AmountValidator::getMinimum())->toBe(0.01);
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

describe('InvalidAmountException Edge Cases', function () {
    it('has error code for not numeric', function () {
        try {
            throw InvalidAmountException::notNumeric('abc');
        } catch (InvalidAmountException $e) {
            expect($e->getCode())->toBe(InvalidAmountException::CODE_NOT_NUMERIC);
            expect($e->getMessage())->toContain('abc');
        }
    });

    it('has error code for zero amount', function () {
        try {
            throw InvalidAmountException::zero();
        } catch (InvalidAmountException $e) {
            expect($e->getCode())->toBe(InvalidAmountException::CODE_ZERO);
            expect($e->getMessage())->toContain('zero');
        }
    });

    it('has error code for too small amount', function () {
        try {
            throw InvalidAmountException::tooSmall(0.001, 0.01);
        } catch (InvalidAmountException $e) {
            expect($e->getCode())->toBe(InvalidAmountException::CODE_TOO_SMALL);
            expect($e->getMessage())->toContain('0.00');
            expect($e->getMessage())->toContain('0.01');
        }
    });

    it('formats negative amount in exception message', function () {
        try {
            throw InvalidAmountException::negative(-50.75);
        } catch (InvalidAmountException $e) {
            expect($e->getMessage())->toContain('-50.75');
            expect($e->getMessage())->toContain('THB');
        }
    });

    it('formats large amount in exception message', function () {
        try {
            throw InvalidAmountException::tooLarge(10000000, 9999999.99);
        } catch (InvalidAmountException $e) {
            expect($e->getMessage())->toContain('10,000,000.00');
            expect($e->getMessage())->toContain('9,999,999.99');
        }
    });
});

describe('InvalidRecipientException Edge Cases', function () {
    it('has error code for not numeric', function () {
        try {
            throw InvalidRecipientException::notNumeric('abc123');
        } catch (InvalidRecipientException $e) {
            expect($e->getCode())->toBe(InvalidRecipientException::CODE_NOT_NUMERIC);
            expect($e->getMessage())->toContain('abc123');
        }
    });

    it('has error code for invalid length', function () {
        try {
            throw InvalidRecipientException::invalidLength('12345', 5);
        } catch (InvalidRecipientException $e) {
            expect($e->getCode())->toBe(InvalidRecipientException::CODE_INVALID_LENGTH);
            expect($e->getMessage())->toContain('5 digits');
        }
    });

    it('has error code for empty after normalization', function () {
        try {
            throw InvalidRecipientException::emptyAfterNormalization('---');
        } catch (InvalidRecipientException $e) {
            expect($e->getCode())->toBe(InvalidRecipientException::CODE_EMPTY_AFTER_NORMALIZATION);
            expect($e->getMessage())->toContain('---');
        }
    });

    it('provides helpful suggestions for too short recipient', function () {
        try {
            throw InvalidRecipientException::invalidLength('123', 3);
        } catch (InvalidRecipientException $e) {
            expect($e->getMessage())->toContain('Too short');
            expect($e->getMessage())->toContain('Phone Number: 10 digits');
        }
    });

    it('provides helpful suggestions for 11-12 digit recipient', function () {
        try {
            throw InvalidRecipientException::invalidLength('12345678901', 11);
        } catch (InvalidRecipientException $e) {
            expect($e->getMessage())->toContain('Invalid length');
        }
    });

    it('provides helpful suggestions for 14 digit recipient', function () {
        try {
            throw InvalidRecipientException::invalidLength('12345678901234', 14);
        } catch (InvalidRecipientException $e) {
            expect($e->getMessage())->toContain('Close!');
            expect($e->getMessage())->toContain('Add one more digit');
        }
    });

    it('provides helpful suggestions for too long recipient', function () {
        try {
            throw InvalidRecipientException::invalidLength('1234567890123456', 16);
        } catch (InvalidRecipientException $e) {
            expect($e->getMessage())->toContain('Too long');
            expect($e->getMessage())->toContain('Maximum is 15 digits');
        }
    });
});

<?php

use Farzai\PromptPay\Exceptions\ConfigurationException;
use Farzai\PromptPay\Validators\ConfigValidator;
use Farzai\PromptPay\ValueObjects\QrCodeConfig;

describe('ConfigValidator', function () {
    describe('validateSize', function () {
        it('validates valid size', function () {
            expect(fn () => ConfigValidator::validateSize(300))->not->toThrow(ConfigurationException::class);
            expect(fn () => ConfigValidator::validateSize(50))->not->toThrow(ConfigurationException::class);
            expect(fn () => ConfigValidator::validateSize(2000))->not->toThrow(ConfigurationException::class);
        });

        it('throws exception for size too small', function () {
            ConfigValidator::validateSize(49);
        })->throws(ConfigurationException::class, 'too small');

        it('throws exception for size too large', function () {
            ConfigValidator::validateSize(2001);
        })->throws(ConfigurationException::class, 'too large');

        it('validates minimum boundary', function () {
            expect(fn () => ConfigValidator::validateSize(50))->not->toThrow(ConfigurationException::class);
        });

        it('validates maximum boundary', function () {
            expect(fn () => ConfigValidator::validateSize(2000))->not->toThrow(ConfigurationException::class);
        });
    });

    describe('validateMargin', function () {
        it('validates valid margin', function () {
            expect(fn () => ConfigValidator::validateMargin(10))->not->toThrow(ConfigurationException::class);
            expect(fn () => ConfigValidator::validateMargin(0))->not->toThrow(ConfigurationException::class);
            expect(fn () => ConfigValidator::validateMargin(100))->not->toThrow(ConfigurationException::class);
        });

        it('throws exception for margin too small', function () {
            ConfigValidator::validateMargin(-1);
        })->throws(ConfigurationException::class, 'too small');

        it('throws exception for margin too large', function () {
            ConfigValidator::validateMargin(101);
        })->throws(ConfigurationException::class, 'too large');
    });

    describe('validateEncoding', function () {
        it('validates valid encoding UTF-8', function () {
            expect(fn () => ConfigValidator::validateEncoding('UTF-8'))->not->toThrow(ConfigurationException::class);
        });

        it('validates valid encoding ISO-8859-1', function () {
            expect(fn () => ConfigValidator::validateEncoding('ISO-8859-1'))->not->toThrow(ConfigurationException::class);
        });

        it('validates valid encoding ASCII', function () {
            expect(fn () => ConfigValidator::validateEncoding('ASCII'))->not->toThrow(ConfigurationException::class);
        });

        it('throws exception for invalid encoding', function () {
            ConfigValidator::validateEncoding('INVALID-ENCODING');
        })->throws(ConfigurationException::class, 'Invalid encoding');

        it('throws exception with list of valid encodings', function () {
            try {
                ConfigValidator::validateEncoding('INVALID');
            } catch (ConfigurationException $e) {
                expect($e->getMessage())->toContain('UTF-8');
                expect($e->getMessage())->toContain('ISO-8859-1');
                expect($e->getMessage())->toContain('ASCII');
            }
        });
    });

    describe('isRecommendedSize', function () {
        it('returns true for recommended size', function () {
            expect(ConfigValidator::isRecommendedSize(300))->toBeTrue();
            expect(ConfigValidator::isRecommendedSize(200))->toBeTrue();
            expect(ConfigValidator::isRecommendedSize(1000))->toBeTrue();
        });

        it('returns false for size below recommended range', function () {
            expect(ConfigValidator::isRecommendedSize(199))->toBeFalse();
            expect(ConfigValidator::isRecommendedSize(100))->toBeFalse();
        });

        it('returns false for size above recommended range', function () {
            expect(ConfigValidator::isRecommendedSize(1001))->toBeFalse();
            expect(ConfigValidator::isRecommendedSize(1500))->toBeFalse();
        });
    });

    describe('getRecommendedSize', function () {
        it('returns recommended size', function () {
            expect(ConfigValidator::getRecommendedSize())->toBe(300);
        });
    });

    describe('getRecommendedMargin', function () {
        it('returns recommended margin', function () {
            expect(ConfigValidator::getRecommendedMargin())->toBe(10);
        });
    });

    describe('suggestSize', function () {
        it('suggests size for web use case', function () {
            expect(ConfigValidator::suggestSize('web'))->toBe(300);
        });

        it('suggests size for print use case', function () {
            expect(ConfigValidator::suggestSize('print'))->toBe(600);
        });

        it('suggests size for mobile use case', function () {
            expect(ConfigValidator::suggestSize('mobile'))->toBe(300);
        });

        it('suggests size for thumbnail use case', function () {
            expect(ConfigValidator::suggestSize('thumbnail'))->toBe(150);
        });

        it('suggests size for large use case', function () {
            expect(ConfigValidator::suggestSize('large'))->toBe(800);
        });

        it('suggests default size for unknown use case', function () {
            expect(ConfigValidator::suggestSize('unknown'))->toBe(300);
        });
    });
});

describe('ConfigurationException', function () {
    it('has correct error code for size too small', function () {
        try {
            throw ConfigurationException::sizeTooSmall(30, 50);
        } catch (ConfigurationException $e) {
            expect($e->getCode())->toBe(ConfigurationException::CODE_SIZE_TOO_SMALL);
            expect($e->getMessage())->toContain('30px');
            expect($e->getMessage())->toContain('50px');
        }
    });

    it('has correct error code for size too large', function () {
        try {
            throw ConfigurationException::sizeTooLarge(2500, 2000);
        } catch (ConfigurationException $e) {
            expect($e->getCode())->toBe(ConfigurationException::CODE_SIZE_TOO_LARGE);
            expect($e->getMessage())->toContain('2500px');
            expect($e->getMessage())->toContain('2000px');
        }
    });

    it('has correct error code for margin too small', function () {
        try {
            throw ConfigurationException::marginTooSmall(-5, 0);
        } catch (ConfigurationException $e) {
            expect($e->getCode())->toBe(ConfigurationException::CODE_MARGIN_TOO_SMALL);
            expect($e->getMessage())->toContain('-5px');
            expect($e->getMessage())->toContain('0px');
        }
    });

    it('has correct error code for margin too large', function () {
        try {
            throw ConfigurationException::marginTooLarge(150, 100);
        } catch (ConfigurationException $e) {
            expect($e->getCode())->toBe(ConfigurationException::CODE_MARGIN_TOO_LARGE);
            expect($e->getMessage())->toContain('150px');
            expect($e->getMessage())->toContain('100px');
        }
    });

    it('has correct error code for invalid encoding', function () {
        try {
            throw ConfigurationException::invalidEncoding('BAD', ['UTF-8', 'ISO-8859-1']);
        } catch (ConfigurationException $e) {
            expect($e->getCode())->toBe(ConfigurationException::CODE_INVALID_ENCODING);
            expect($e->getMessage())->toContain('BAD');
            expect($e->getMessage())->toContain('UTF-8');
        }
    });

    it('has correct error code for invalid path', function () {
        try {
            throw ConfigurationException::invalidPath('/invalid/path');
        } catch (ConfigurationException $e) {
            expect($e->getCode())->toBe(ConfigurationException::CODE_INVALID_PATH);
            expect($e->getMessage())->toContain('/invalid/path');
        }
    });

    it('has correct error code for missing dependency', function () {
        try {
            throw ConfigurationException::missingDependency('some-package', 'composer require some-package');
        } catch (ConfigurationException $e) {
            expect($e->getCode())->toBe(ConfigurationException::CODE_MISSING_DEPENDENCY);
            expect($e->getMessage())->toContain('some-package');
            expect($e->getMessage())->toContain('composer require');
        }
    });
});

describe('QrCodeConfig Value Object', function () {
    it('creates config with default values', function () {
        $config = QrCodeConfig::default();

        expect($config->getSize())->toBe(300);
        expect($config->getMargin())->toBe(10);
        expect($config->getEncoding())->toBe('UTF-8');
    });

    it('creates config with custom values', function () {
        $config = QrCodeConfig::create(size: 500, margin: 20, encoding: 'ISO-8859-1');

        expect($config->getSize())->toBe(500);
        expect($config->getMargin())->toBe(20);
        expect($config->getEncoding())->toBe('ISO-8859-1');
    });

    it('creates config with partial custom values', function () {
        $config = QrCodeConfig::create(size: 400);

        expect($config->getSize())->toBe(400);
        expect($config->getMargin())->toBe(10);
        expect($config->getEncoding())->toBe('UTF-8');
    });

    it('is immutable when changing size', function () {
        $config1 = QrCodeConfig::create(size: 300);
        $config2 = $config1->withSize(500);

        expect($config1->getSize())->toBe(300);
        expect($config2->getSize())->toBe(500);
        expect($config1)->not->toBe($config2);
    });

    it('is immutable when changing margin', function () {
        $config1 = QrCodeConfig::create(margin: 10);
        $config2 = $config1->withMargin(20);

        expect($config1->getMargin())->toBe(10);
        expect($config2->getMargin())->toBe(20);
        expect($config1)->not->toBe($config2);
    });

    it('is immutable when changing encoding', function () {
        $config1 = QrCodeConfig::create(encoding: 'UTF-8');
        $config2 = $config1->withEncoding('ASCII');

        expect($config1->getEncoding())->toBe('UTF-8');
        expect($config2->getEncoding())->toBe('ASCII');
        expect($config1)->not->toBe($config2);
    });

    it('preserves other values when changing size', function () {
        $config = QrCodeConfig::create(size: 300, margin: 15, encoding: 'ISO-8859-1')
            ->withSize(400);

        expect($config->getSize())->toBe(400);
        expect($config->getMargin())->toBe(15);
        expect($config->getEncoding())->toBe('ISO-8859-1');
    });

    it('can chain multiple modifications', function () {
        $config = QrCodeConfig::default()
            ->withSize(400)
            ->withMargin(15)
            ->withEncoding('ASCII');

        expect($config->getSize())->toBe(400);
        expect($config->getMargin())->toBe(15);
        expect($config->getEncoding())->toBe('ASCII');
    });
});

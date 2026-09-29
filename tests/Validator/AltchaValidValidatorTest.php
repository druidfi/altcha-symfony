<?php

namespace Druidfi\AltchaSymfony\Tests\Validator;

use Druidfi\AltchaSymfony\Service\AltchaService;
use Druidfi\AltchaSymfony\Validator\AltchaValid;
use Druidfi\AltchaSymfony\Validator\AltchaValidValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<AltchaValidValidator>
 */
class AltchaValidValidatorTest extends ConstraintValidatorTestCase
{
    // -------------------------------------------------------------------------
    // Infrastructure
    // -------------------------------------------------------------------------

    protected function createValidator(): AltchaValidValidator
    {
        // Default validator: CAPTCHA enabled, service returns true (valid payload).
        return new AltchaValidValidator($this->passingService());
    }

    /**
     * Replaces the active validator mid-test so individual tests can use a
     * different enabled flag or service behaviour without a separate test class.
     */
    private function useValidator(AltchaValidValidator $validator): void
    {
        $this->validator = $validator;
        $this->validator->initialize($this->context);
    }

    private function passingService(): AltchaService
    {
        $mock = $this->createMock(AltchaService::class);
        $mock->method('verify')->willReturn(true);
        return $mock;
    }

    private function failingService(): AltchaService
    {
        $mock = $this->createMock(AltchaService::class);
        $mock->method('verify')->willReturn(false);
        return $mock;
    }

    // -------------------------------------------------------------------------
    // ALTCHA_ENABLED flag
    // -------------------------------------------------------------------------

    /** @dataProvider disabledFlagProvider */
    public function testValidationSkippedWhenDisabled(string $flag): void
    {
        // Use a failing service to prove verify() is never the deciding factor.
        $this->useValidator(new AltchaValidValidator($this->failingService(), $flag));

        $this->validator->validate('any-payload', new AltchaValid());

        $this->assertNoViolation();
    }

    public static function disabledFlagProvider(): array
    {
        return [
            'false lowercase' => ['false'],
            'false uppercase' => ['FALSE'],
            'false mixed case' => ['False'],
            'zero'            => ['0'],
        ];
    }

    /** @dataProvider enabledFlagProvider */
    public function testValidationRunsWhenEnabled(?string $flag): void
    {
        // Use a failing service: if validation runs, a violation must appear.
        $this->useValidator(new AltchaValidValidator($this->failingService(), $flag));

        $this->validator->validate('some-payload', new AltchaValid());

        $this->buildViolation('The CAPTCHA is invalid.')->assertRaised();
    }

    public static function enabledFlagProvider(): array
    {
        return [
            'null (not set)'   => [null],
            'empty string'     => [''],
            'true'             => ['true'],
            '1'                => ['1'],
        ];
    }

    // -------------------------------------------------------------------------
    // Empty payload short-circuit
    // -------------------------------------------------------------------------

    public function testEmptyPayloadAddsViolationWithoutCallingVerify(): void
    {
        $service = $this->createMock(AltchaService::class);
        $service->expects($this->never())->method('verify');
        $this->useValidator(new AltchaValidValidator($service, null));

        $this->validator->validate('', new AltchaValid());

        $this->buildViolation('The CAPTCHA is invalid.')->assertRaised();
    }

    public function testNullPayloadAddsViolationWithoutCallingVerify(): void
    {
        $service = $this->createMock(AltchaService::class);
        $service->expects($this->never())->method('verify');
        $this->useValidator(new AltchaValidValidator($service, null));

        $this->validator->validate(null, new AltchaValid());

        $this->buildViolation('The CAPTCHA is invalid.')->assertRaised();
    }

    // -------------------------------------------------------------------------
    // verify() outcome
    // -------------------------------------------------------------------------

    public function testValidPayloadPassesValidation(): void
    {
        // Default validator uses passingService() — no violation expected.
        $this->validator->validate('valid-payload', new AltchaValid());

        $this->assertNoViolation();
    }

    public function testInvalidPayloadAddsViolation(): void
    {
        $this->useValidator(new AltchaValidValidator($this->failingService(), null));

        $this->validator->validate('invalid-payload', new AltchaValid());

        $this->buildViolation('The CAPTCHA is invalid.')->assertRaised();
    }

    // -------------------------------------------------------------------------
    // Custom message
    // -------------------------------------------------------------------------

    public function testCustomConstraintMessageIsUsed(): void
    {
        $this->useValidator(new AltchaValidValidator($this->failingService(), null));

        $this->validator->validate('bad', new AltchaValid(message: 'Please solve the CAPTCHA.'));

        $this->buildViolation('Please solve the CAPTCHA.')->assertRaised();
    }
}

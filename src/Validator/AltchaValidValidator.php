<?php

namespace Druidfi\AltchaSymfony\Validator;

use Druidfi\AltchaSymfony\Service\AltchaService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AltchaValidValidator extends ConstraintValidator
{
    public function __construct(private readonly AltchaService $altchaService) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AltchaValid) {
            throw new UnexpectedTypeException($constraint, AltchaValid::class);
        }

        // empty() short-circuits before the verify() network/HMAC call:
        // a solved widget always submits a non-empty base64 payload, so an empty value
        // means the user never completed the CAPTCHA.
        if (empty($value) || !$this->altchaService->verify((string) $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}

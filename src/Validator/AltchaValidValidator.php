<?php

namespace Druidfi\AltchaSymfony\Validator;

use Druidfi\AltchaSymfony\Service\AltchaService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AltchaValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly AltchaService $altchaService,
        #[Autowire(value: '%env(default::ALTCHA_ENABLED)%')]
        private readonly ?string $enabled = null,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AltchaValid) {
            throw new UnexpectedTypeException($constraint, AltchaValid::class);
        }

        // When ALTCHA_ENABLED=false (or 0), skip validation entirely — useful in test/dev environments.
        if ($this->enabled !== null && $this->enabled !== '' && in_array(strtolower($this->enabled), ['false', '0'], true)) {
            return;
        }

        // empty() short-circuits before the verify() network/HMAC call:
        // a solved widget always submits a non-empty base64 payload, so an empty value
        // means the user never completed the CAPTCHA.
        if (empty($value) || !$this->altchaService->verify((string) $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}

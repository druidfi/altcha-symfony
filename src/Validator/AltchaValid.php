<?php

namespace Druidfi\AltchaSymfony\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\HasNamedArguments;

/**
 * Marks a form field value as requiring a valid Altcha CAPTCHA payload.
 *
 * Usage: applied automatically by AltchaType via configureOptions().
 * Can also be used manually: #[AltchaValid] or new AltchaValid(message: '...').
 */
#[\Attribute]
#[HasNamedArguments]
class AltchaValid extends Constraint
{
    public function __construct(
        public string $message = 'The CAPTCHA is invalid.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }
}

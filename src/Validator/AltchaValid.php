<?php

namespace Druidfi\AltchaSymfony\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AltchaValid extends Constraint
{
    public string $message = 'The CAPTCHA is invalid.';
}

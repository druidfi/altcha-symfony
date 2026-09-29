<?php

namespace Druidfi\AltchaSymfony\Tests\Validator;

use Druidfi\AltchaSymfony\Validator\AltchaValid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\HasNamedArguments;

class AltchaValidTest extends TestCase
{
    public function testExtendsConstraint(): void
    {
        $this->assertInstanceOf(Constraint::class, new AltchaValid());
    }

    public function testDefaultMessage(): void
    {
        $constraint = new AltchaValid();

        $this->assertSame('The CAPTCHA is invalid.', $constraint->message);
    }

    public function testCustomMessage(): void
    {
        $constraint = new AltchaValid(message: 'Please solve the CAPTCHA.');

        $this->assertSame('Please solve the CAPTCHA.', $constraint->message);
    }

    public function testHasNamedArgumentsAttribute(): void
    {
        $reflection = new \ReflectionClass(AltchaValid::class);
        $attributes = $reflection->getAttributes(HasNamedArguments::class);

        $this->assertNotEmpty($attributes, AltchaValid::class . ' must carry #[HasNamedArguments] to avoid Symfony 7.3+ deprecation.');
    }

    public function testIsPhpAttribute(): void
    {
        $reflection = new \ReflectionClass(AltchaValid::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertNotEmpty($attributes, AltchaValid::class . ' must be usable as a PHP attribute.');
    }

    public function testGroupsCanBePassedToConstructor(): void
    {
        $constraint = new AltchaValid(groups: ['my_group']);

        $this->assertContains('my_group', $constraint->groups);
    }
}

<?php

namespace Druidfi\AltchaSymfony\Tests\Form\Type;

use Druidfi\AltchaSymfony\Form\Type\AltchaType;
use Druidfi\AltchaSymfony\Service\AltchaService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AltchaTypeTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Factory helper
    // -------------------------------------------------------------------------

    private function makeType(?string $floating): AltchaType
    {
        return new AltchaType(
            altchaService: $this->createMock(AltchaService::class),
            router: $this->createMock(UrlGeneratorInterface::class),
            hideFooter: true,
            hideLogo: true,
            scriptUrl: '/js/altcha.js',
            auto: null,
            floating: $floating,
        );
    }

    private function invokeResolveFloating(AltchaType $type): bool
    {
        $method = new \ReflectionMethod($type, 'resolveFloating');
        return $method->invoke($type);
    }

    // -------------------------------------------------------------------------
    // resolveFloating() — env var resolution
    // -------------------------------------------------------------------------

    /** @dataProvider floatingEnvProvider */
    public function testResolveFloating(?string $envValue, bool $expected): void
    {
        $this->assertSame($expected, $this->invokeResolveFloating($this->makeType($envValue)));
    }

    public static function floatingEnvProvider(): array
    {
        return [
            'null (not set) → false'      => [null,    false],
            'empty string → false'         => ['',      false],
            'true → true'                  => ['true',  true],
            'TRUE uppercase → true'        => ['TRUE',  true],
            '1 → true'                     => ['1',     true],
            'false → false'                => ['false', false],
            'FALSE uppercase → false'      => ['FALSE', false],
            '0 → false'                    => ['0',     false],
            'arbitrary string → false'     => ['yes',   false],
        ];
    }

    // -------------------------------------------------------------------------
    // getParent / getBlockPrefix
    // -------------------------------------------------------------------------

    public function testGetBlockPrefix(): void
    {
        $type = $this->makeType(null);

        $this->assertSame('altcha', $type->getBlockPrefix());
    }
}

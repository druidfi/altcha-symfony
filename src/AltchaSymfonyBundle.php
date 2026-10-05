<?php

namespace Druidfi\AltchaSymfony;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AltchaSymfonyBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()
            ->defaults()
                ->autowire()
                ->autoconfigure()
            ->load('Druidfi\\AltchaSymfony\\', '../src/')
            ->exclude([
                // Not a service — it is the bundle class itself.
                '../src/AltchaSymfonyBundle.php',
                // Constraint data class, not a service.
                '../src/Validator/AltchaValid.php',
            ]);
    }
}

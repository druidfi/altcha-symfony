<?php

namespace Druidfi\AltchaSymfony\Form\Type;

use Druidfi\AltchaSymfony\Service\AltchaService;
use Druidfi\AltchaSymfony\Validator\AltchaValid;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Symfony form type for the Altcha CAPTCHA widget.
 *
 * Renders an <altcha-widget> custom element backed by a hidden input.
 * The solved payload submitted by the widget is validated server-side
 * via AltchaValid, which delegates to AltchaService::verify().
 */
class AltchaType extends AbstractType
{
    public function __construct(
        private readonly AltchaService $altchaService,
        private readonly UrlGeneratorInterface $router,
        #[Autowire(value: '%env(bool:ALTCHA_HIDE_FOOTER)%')]
        private readonly bool $hideFooter,
        #[Autowire(value: '%env(bool:ALTCHA_HIDE_LOGO)%')]
        private readonly bool $hideLogo,
        #[Autowire(value: '%env(ALTCHA_SCRIPT_URL)%')]
        private readonly string $scriptUrl,
        #[Autowire(value: '%env(default::ALTCHA_AUTO)%')]
        private readonly ?string $auto = null,
    ) {}

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Skip route generation in sentinel mode — the sentinel URL is used directly.
        $localUrl = $this->altchaService->isSentinel() ? '' : $this->router->generate('altcha_challenge');
        $view->vars['altcha_challenge_url'] = $this->altchaService->getChallengeUrl($localUrl);
        $view->vars['altcha_hide_footer'] = $this->hideFooter;
        $view->vars['altcha_hide_logo'] = $this->hideLogo;
        $view->vars['altcha_script_url'] = $this->scriptUrl;
        $view->vars['altcha_auto'] = $this->auto;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // The widget value is verified separately — it should not be mapped to any model property.
            'mapped' => false,
            // required: false prevents HTML5 browser validation on the hidden input; server-side
            // validation via AltchaValid handles the actual requirement.
            'required' => false,
            'constraints' => [new AltchaValid()],
        ]);
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'altcha';
    }
}

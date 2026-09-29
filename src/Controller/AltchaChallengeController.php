<?php

namespace Druidfi\AltchaSymfony\Controller;

use Druidfi\AltchaSymfony\Service\AltchaService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Serves proof-of-work challenges for self-hosted mode.
 *
 * In sentinel mode the widget fetches challenges directly from the Sentinel API,
 * so this endpoint is never called — but it remains registered and harmless.
 * Ensure this route is publicly accessible (no firewall restriction).
 */
class AltchaChallengeController
{
    public function __construct(private readonly AltchaService $altchaService) {}

    #[Route('/altcha/challenge', name: 'altcha_challenge', methods: ['GET'])]
    public function __invoke(): Response
    {
        $challenge = $this->altchaService->createChallenge();

        return new JsonResponse(
            data: $challenge->toArray(),
            headers: [
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
            ]
        );
    }
}

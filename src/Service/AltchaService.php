<?php

namespace Druidfi\AltchaSymfony\Service;

use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Challenge;
use AltchaOrg\Altcha\CreateChallengeOptions;
use AltchaOrg\Altcha\Sentinel;
use AltchaOrg\Altcha\VerifyServerOptions;
use AltchaOrg\Altcha\VerifySolutionOptions;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Handles Altcha CAPTCHA challenge creation and solution verification.
 *
 * Two modes are supported:
 *   - Self-hosted: challenges are generated and verified locally using HMAC + PBKDF2.
 *                  No external network calls. Requires ALTCHA_HMAC_KEY.
 *   - Sentinel:    challenge URL and verification are delegated to Altcha's cloud/sentinel API.
 *                  Activated by setting ALTCHA_SENTINEL_URL to a non-empty value.
 */
class AltchaService
{
    private readonly Altcha $altcha;

    public function __construct(
        #[Autowire(env: 'ALTCHA_HMAC_KEY')]
        private readonly string $hmacKey,
        #[Autowire(value: '%env(default::ALTCHA_SENTINEL_URL)%')]
        private readonly ?string $sentinelUrl = null,
        #[Autowire(value: '%env(default::ALTCHA_SENTINEL_VERIFY_URL)%')]
        private readonly ?string $sentinelVerifyUrl = null,
        #[Autowire(value: '%env(default::ALTCHA_SENTINEL_API_KEY)%')]
        private readonly ?string $sentinelApiKey = null,
    ) {
        $this->altcha = new Altcha(hmacSignatureSecret: $this->hmacKey);
    }

    public function isSentinel(): bool
    {
        return $this->sentinelUrl !== null && $this->sentinelUrl !== '';
    }

    /**
     * Returns the challenge URL for the altcha-widget.
     * Always a URL (never inline JSON) so self-hosted and sentinel/cloud both work.
     */
    public function getChallengeUrl(string $localUrl): string
    {
        return $this->isSentinel() ? ($this->sentinelUrl ?? '') : $localUrl;
    }

    public function createChallenge(): Challenge
    {
        return $this->altcha->createChallenge(new CreateChallengeOptions(
            algorithm: new Pbkdf2(),
            // 50 000 PBKDF2 iterations: high enough to slow bots, fast enough for real users (~1 s on modern hardware).
            cost: 50000,
            // Challenges expire after 10 minutes to prevent reuse of old solved payloads.
            expiresAt: time() + 600,
        ));
    }

    public function verify(string $payload): bool
    {
        if ($this->isSentinel()) {
            $verifyUrl = ($this->sentinelVerifyUrl !== null && $this->sentinelVerifyUrl !== '') ? $this->sentinelVerifyUrl : $this->deriveSentinelVerifyUrl();

            try {
                $result = Sentinel::verify(new VerifyServerOptions(
                    payload: $payload,
                    url: $verifyUrl,
                    secret: ($this->sentinelApiKey !== null && $this->sentinelApiKey !== '') ? $this->sentinelApiKey : null,
                ));
            } catch (\Throwable) {
                // Network errors or sentinel API failures return false so the form shows
                // a CAPTCHA validation error instead of throwing a 500.
                return false;
            }

            return $result->verified;
        }

        try {
            $result = $this->altcha->verifySolution(new VerifySolutionOptions(
                payload: $payload,
                algorithm: new Pbkdf2(),
            ));
        } catch (\Throwable) {
            // Malformed or tampered payloads (bad base64, invalid JSON) cause the library to throw.
            // Treat as verification failure rather than propagating a 500.
            return false;
        }

        return $result->verified;
    }

    /**
     * Derives a verify URL from the sentinel challenge URL as a fallback.
     * e.g. https://eu.altcha.org/api/v1/challenge?apiKey=xxx -> https://eu.altcha.org/api/v1/verify/signature
     */
    private function deriveSentinelVerifyUrl(): string
    {
        $parsed = parse_url($this->sentinelUrl);

        if (!is_array($parsed) || empty($parsed['scheme']) || empty($parsed['host'])) {
            throw new \InvalidArgumentException('ALTCHA_SENTINEL_URL is not a valid absolute URL — check your environment configuration.');
        }

        return sprintf('%s://%s/api/v1/verify/signature', $parsed['scheme'], $parsed['host']);
    }
}

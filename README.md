# Altcha CAPTCHA integration

[Altcha](https://altcha.org) is a privacy-friendly, self-hostable CAPTCHA. This integration supports both **self-hosted** (proof-of-work, no external calls) and **Sentinel/cloud** (server-side verification via Altcha's API) modes.

## Files

| File | Purpose |
|---|---|
| `src/Service/AltchaService.php` | Core logic: create challenges, verify solutions |
| `src/Controller/AltchaChallengeController.php` | `GET /altcha/challenge` endpoint (self-hosted mode) |
| `src/Form/Type/AltchaType.php` | Symfony form type — renders the widget and applies validation |
| `src/Validator/AltchaValid.php` | Constraint attribute |
| `src/Validator/AltchaValidValidator.php` | Constraint validator — calls `AltchaService::verify()` |
| `templates/altcha_form.html.twig` | Twig block that renders the `<altcha-widget>` element |
| `public/js/altcha.js` | Vendored Altcha widget JS (used in self-hosted mode) |
| `public/js/altcha-i18n.js` | Finnish (`fi`) and Swedish (`sv`) translations for the widget |

## Environment variables

All variables are defined in `.env`. Override them per environment in `.env.local` / `.env.prod` etc.

| Variable | Default | Description |
|---|---|---|
| `ALTCHA_HMAC_KEY` | `dev-only-hmac-key-change-in-production` | HMAC secret for signing challenges. **Must be changed in production.** |
| `ALTCHA_ENABLED` | _(empty)_ | Set to `false` or `0` to disable CAPTCHA validation entirely. Useful in test/dev environments. |
| `ALTCHA_COST` | `50000` | PBKDF2 iteration count for proof-of-work challenges. Higher = harder for bots, slower for users. |
| `ALTCHA_SENTINEL_URL` | _(empty)_ | Set to enable Sentinel/cloud mode. Base challenge URL handed to the widget. |
| `ALTCHA_SENTINEL_VERIFY_URL` | _(empty)_ | Optional override for the Sentinel verify endpoint. Derived from `ALTCHA_SENTINEL_URL` if omitted. |
| `ALTCHA_SENTINEL_API_KEY` | _(empty)_ | API key appended to `ALTCHA_SENTINEL_URL` as `?apiKey=`. Allows the URL and key to be stored as separate secrets. |
| `ALTCHA_SENTINEL_API_SECRET` | _(empty)_ | API secret sent in the Sentinel verify request body. Distinct from the API key. |
| `ALTCHA_HIDE_FOOTER` | `true` | Hide the "Powered by Altcha" footer in the widget. |
| `ALTCHA_HIDE_LOGO` | `true` | Hide the Altcha logo in the widget. |
| `ALTCHA_SCRIPT_URL` | `/js/altcha.js` | URL to the Altcha widget JS. Use `/js/altcha.js` for the vendored local copy or a CDN URL. |
| `ALTCHA_AUTO` | _(empty)_ | Auto-solve mode: `onload` (invisible, solves on page load), `onsubmit` (solves on form submit), or empty for manual checkbox (default). |
| `ALTCHA_FLOATING` | _(empty)_ | Set to `true` or `1` to show the widget as a floating badge instead of an inline element. |

## Modes

### Self-hosted (default)

All three `ALTCHA_SENTINEL_*` variables are empty. The widget fetches a challenge from `/altcha/challenge`, solves it client-side using proof-of-work (PBKDF2), and submits the solution in a hidden form field. The server verifies the HMAC signature locally — no external calls.

### Sentinel / cloud

Set `ALTCHA_SENTINEL_URL` to your Sentinel challenge base URL (e.g. `https://eu.altcha.org/api/v1/challenge`). Set `ALTCHA_SENTINEL_API_KEY` to your API key — it will be appended as `?apiKey=` automatically. This allows the URL and key to be stored as separate secrets in a vault. Alternatively, you can embed the key directly in `ALTCHA_SENTINEL_URL` and leave `ALTCHA_SENTINEL_API_KEY` empty.

`ALTCHA_SENTINEL_VERIFY_URL` is optional — if omitted, the verify URL is derived from `ALTCHA_SENTINEL_URL` by replacing the path with `/api/v1/verify/signature`.

`ALTCHA_SENTINEL_API_SECRET` is the API secret sent in the verify request body (`secret` field). It is separate from the API key.

## Usage in a form

Add the field to any Symfony form class:

```php
use Druidfi\AltchaSymfony\Form\Type\AltchaType;

$builder->add('altcha', AltchaType::class);
```

The form type renders the widget via `templates/altcha_form.html.twig` and automatically attaches an `AltchaValid` constraint. No extra configuration is needed.

To show the floating badge style on a specific form, pass the `floating` option:

```php
$builder->add('altcha', AltchaType::class, ['floating' => true]);
```

This overrides `ALTCHA_FLOATING` for that field only.

## Translations

The Altcha widget bundles only English. `public/js/altcha-i18n.js` registers Finnish (`fi`) and Swedish (`sv`) translations via `globalThis.$altcha?.i18n.set()`, which the widget picks up reactively.

The widget resolves the display language in order: `language` attribute on `<altcha-widget>` → `document.documentElement.lang` → `navigator.languages`. The `<html lang="{{ app.request.locale }}">` attribute in `base.html.twig` provides the locale automatically.

To add another language, append a translation object to `altcha-i18n.js` and call `globalThis.$altcha?.i18n.set('<locale>', obj)`. See the existing `fi`/`sv` objects for the full list of required keys.

## Updating the vendored JS

The file `public/js/altcha.js` is a vendored copy of the Altcha widget. To update it, replace it with the latest `altcha.js` from the [Altcha releases](https://github.com/altcha-org/altcha/releases) or from npm (`altcha` package, `dist/main/altcha.js`), then set `ALTCHA_SCRIPT_URL=/js/altcha.js`.

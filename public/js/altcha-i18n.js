// Altcha widget translations for Finnish and Swedish.
// Loaded after altcha.js — the reactive i18n store triggers a re-render automatically.

const fi = {
    ariaLinkLabel: 'Altcha (virallinen verkkosivusto)',
    cancel: 'Peruuta',
    enterCode: 'Syötä koodi',
    enterCodeAria: 'Syötä kuulemasi koodi. Paina välilyöntiä toistaaksesi äänen.',
    enterCodeFromImage: 'Jatkaaksesi syötä alla olevan kuvan koodi.',
    error: 'Vahvistus epäonnistui. Yritä myöhemmin uudelleen.',
    expired: 'Vahvistus on vanhentunut. Yritä uudelleen.',
    footer: 'Suojattu <a href="https://altcha.org/" tabindex="-1" target="_blank" rel="noopener" aria-label="Altcha (virallinen verkkosivusto)">ALTCHA</a>:lla',
    getAudioChallenge: 'Hanki äänitehtävä',
    label: 'En ole robotti',
    loading: 'Ladataan\u2026',
    reload: 'Lataa uudelleen',
    verify: 'Vahvista',
    verificationRequired: 'Vahvistus vaaditaan!',
    verified: 'Vahvistettu',
    verifying: 'Vahvistetaan\u2026',
    waitAlert: 'Vahvistetaan\u2026 odota hetki.',
};

const sv = {
    ariaLinkLabel: 'Altcha (officiell webbplats)',
    cancel: 'Avbryt',
    enterCode: 'Ange kod',
    enterCodeAria: 'Ange koden du h\u00f6r. Tryck p\u00e5 mellanslag f\u00f6r att spela upp ljud.',
    enterCodeFromImage: 'Ange koden fr\u00e5n bilden nedan f\u00f6r att forts\u00e4tta.',
    error: 'Verifieringen misslyckades. F\u00f6rs\u00f6k igen senare.',
    expired: 'Verifieringen har upph\u00f6rt. F\u00f6rs\u00f6k igen.',
    footer: 'Skyddad av <a href="https://altcha.org/" tabindex="-1" target="_blank" rel="noopener" aria-label="Altcha (officiell webbplats)">ALTCHA</a>',
    getAudioChallenge: 'F\u00e5 en ljudutmaning',
    label: 'Jag \u00e4r inte en robot',
    loading: 'Laddar\u2026',
    reload: 'Ladda om',
    verify: 'Verifiera',
    verificationRequired: 'Verifiering kr\u00e4vs!',
    verified: 'Verifierad',
    verifying: 'Verifierar\u2026',
    waitAlert: 'Verifierar\u2026 v\u00e4nta.',
};

globalThis.$altcha?.i18n.set('fi', fi);
globalThis.$altcha?.i18n.set('sv', sv);

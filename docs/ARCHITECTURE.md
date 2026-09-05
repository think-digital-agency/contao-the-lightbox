# ARCHITECTURE.md – ContaoTheLightboxBundle

## Directory Tree

```
packages/contao-the-lightbox-bundle/
├── composer.json
├── LICENSE                                         # LGPL-3.0-or-later
├── CHANGELOG.md
├── README.md
├── docs/
│   ├── ARCHITECTURE.md
│   ├── DECISIONS.md
│   └── package-metadata/{de,en}.yml
├── public/                                         # symlinked to public/bundles/contaothelightbox/
│   ├── css/
│   │   └── glightbox.min.css                       # vendored GLightbox 3.3.1 stylesheet
│   └── js/
│       ├── glightbox.min.js                        # vendored GLightbox 3.3.1 library (UMD, global `GLightbox`)
│       ├── glightbox.LICENSE.md                    # upstream MIT license
│       └── lightbox.js                             # init script — the whole integration
└── src/
    ├── ContaoTheLightboxBundle.php                 # Bundle entry point, getPath() => dirname(__DIR__)
    ├── ContaoManager/
    │   └── Plugin.php                              # BundlePluginInterface — registers bundle after ContaoCoreBundle
    ├── DependencyInjection/
    │   └── ContaoTheLightboxExtension.php          # DI extension, loads services.yaml
    ├── EventListener/
    │   └── RegisterLightboxAssetsListener.php      # #[AsHook('generatePage')] — adds CSS + JS to every FE page
    ├── Migration/
    │   └── RemoveGLightboxLayoutScriptMigration.php # strips `js_glightbox` from tl_layout.scripts
    └── Resources/config/
        └── services.yaml                           # autowired service scan
```

---

## Symfony Service Graph

| Service | Class | Dependencies | Registration |
|---|---|---|---|
| `RegisterLightboxAssetsListener` | `EventListener\RegisterLightboxAssetsListener` | — | `#[AsHook('generatePage')]` |
| `RemoveGLightboxLayoutScriptMigration` | `Migration\RemoveGLightboxLayoutScriptMigration` | `Doctrine\DBAL\Connection` | `MigrationInterface` autoconfigure |

Both are picked up by `autoconfigure: true` in `services.yaml` — no manual service definitions.

---

## Runtime Flow

1. **`generatePage` hook** (`RegisterLightboxAssetsListener`) runs on every front end
   page and registers three assets:
   - `$GLOBALS['TL_CSS']['contao-the-lightbox']` → `bundles/contaothelightbox/css/glightbox.min.css`
   - `$GLOBALS['TL_JAVASCRIPT']['contao-the-lightbox-lib']` → `bundles/contaothelightbox/js/glightbox.min.js`
   - `$GLOBALS['TL_JAVASCRIPT']['contao-the-lightbox-init']` → `bundles/contaothelightbox/js/lightbox.js`

   String keys deduplicate the entries if the hook somehow runs twice.

2. Contao's `Combiner` merges these into the combined `<head>` `<link>` / `<script>`
   bundles. `ReplaceDynamicScriptTagsListener` injects them before `</head>`
   **regardless of what the page template renders** — this is why the bundle works
   in the Design+ theme, whose `fe_page.html.twig` omits `{{ scripts }}` /
   `{{ mootools }}`.

3. **`lightbox.js`** waits for `DOMContentLoaded` (the combined script tag is in
   `<head>`, so the DOM is not ready yet), then:
   - copies every `a[data-lightbox]` link's `data-lightbox` value onto
     `data-gallery` (random group id when the value is empty), and
   - calls `GLightbox({ selector: 'a[data-lightbox]' })`.

   This is a 1:1 port of the inline script from the legacy `js_glightbox` template.

---

## The `data-lightbox` attribute

`data-lightbox="lbNNN"` on fullsize image links is emitted by **Contao core**
(`Figure` / `enableLightbox()` when `fullsize=1` on an image). It is not produced
by this bundle and not by the old glightbox bundle. This bundle only *consumes* it.

---

## Contao 6 compatibility

`contao/core-bundle: ^5.3 || ^6.0`. The bundle touches no template engine and no
removed API: only `$GLOBALS['TL_CSS']` / `$GLOBALS['TL_JAVASCRIPT']` (stable in 6),
the `generatePage` hook (still supported in 6), `AbstractMigration` and DBAL.

---

## What this bundle does NOT include

- A backend UI, DCA changes or new fields.
- A per-layout or per-page on/off switch — the assets load on every front end page.
  A future `config` toggle is possible (DECISIONS ADR-004).
- A replacement `js_glightbox` template — the layout-script mechanism is dropped
  entirely and cleaned out of `tl_layout` by the migration.
- Any customisation of GLightbox options — `lightbox.js` uses the library defaults
  plus `selector`. Fork the file to change them.
- The `data-lightbox` attribute on image links (Contao core emits it).

# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [1.0.0] - 2026-09-03

Initial release — replaces `inspiredminds/contao-glightbox` in the Contao Design+
theme (Contao 6 migration, WP-4).

### Added
- Vendored GLightbox 3.3.1 library and stylesheet (MIT), served as
  `bundles/contaothelightbox/`
- `RegisterLightboxAssetsListener` — `generatePage` hook that loads the stylesheet,
  the library and the init script on every front end page (no layout config needed)
- `public/js/lightbox.js` — 1:1 port of the legacy `js_glightbox` inline script
  (`data-lightbox` → `data-gallery`, random group when empty, `GLightbox()` init),
  deferred to `DOMContentLoaded`
- `RemoveGLightboxLayoutScriptMigration` — strips the legacy `js_glightbox` entry
  from `tl_layout.scripts` (key-matched, idempotent)
- Dual compatibility: `contao/core-bundle: ^5.3 || ^6.0`

### Fixed
- The lightbox did not initialise in themes whose page template omits
  `{{ scripts }}` / `{{ mootools }}` (e.g. Design+): the library and init script
  were never emitted, only the stylesheet. The `generatePage` hook makes asset
  loading independent of the page template.

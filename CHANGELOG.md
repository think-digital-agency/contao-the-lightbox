# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [3.0.2] - 2026-09-06

### Documentation
- DECISIONS.md references the theme's `DECISIONS.md` instead of its old migration doc.

## [3.0.1] - 2026-09-05

### Changed
- Removed remaining references to the previous lightbox extension from code
  comments and docs (kept: the `js_glightbox` layout-script value itself,
  which is a real config value the migration matches against, not a package
  reference).
- Removed `docs/PUBLISHING.md` — publishing is complete, and the two sibling
  bundles never carried this file either.

## [3.0.0] - 2026-09-05

Initial release — built for the Contao Design+ theme (Contao 6 migration, WP-4)
as a drop-in GLightbox integration. Package version starts at the vendored
GLightbox major version (3.x) rather than at 1.0.0 — see `docs/DECISIONS.md`
ADR-006.

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

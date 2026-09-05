# DECISIONS.md – Architecture Decision Records

Bundle: **Contao The Lightbox** (`think-digital-agency/contao-the-lightbox`).
Built for WP-4 of the Design+ Contao 6 migration (see `CONTAO6_MIGRATION.md`,
ADR-6). Replaces `inspiredminds/contao-glightbox`.

---

## ADR-001: GLightbox JS + CSS are vendored into the bundle (`public/`)

**Date:** 2026-09-03
**Status:** Accepted

**Context:**
`inspiredminds/contao-glightbox` shipped the GLightbox build in its own
`public/` folder (served as `bundles/contaoglightbox/…`). The Design+ theme
additionally carried an unused, *different* GLightbox build under
`files/theme/theme-design/js/01-libraries/glightbox.js/` (never `@import`ed, never
registered — dead weight). Three options: (a) vendor the assets in the new
bundle, (b) point the bundle at the theme's file copy, (c) ship only JS and keep
relying on the old CSS side effect.

**Decision:**
(a) — `public/css/glightbox.min.css` + `public/js/glightbox.min.js`, the
**GLightbox 3.3.1** dist build (latest stable; 4.0 is beta only), fetched verbatim
from the upstream npm package (biati-digital/glightbox, MIT — license kept at
`public/js/glightbox.LICENSE.md`). Served as `bundles/contaothelightbox/…`. Same
model as the old bundle and as `think-digital-agency/contao-live-preview`.

Upgrading the library = drop the new `dist/js/glightbox.min.js` +
`dist/css/glightbox.min.css` in, update this ADR and the CHANGELOG. No build step.

**Consequences:**
- (+) Self-contained and theme-independent — the bundle works in any Contao project.
- (+) The library version is pinned and versioned together with the bundle.
- (+) The theme's dead `js/01-libraries/glightbox.js/` copy is deleted.
- (−) The build is duplicated on disk if another extension also ships GLightbox —
  acceptable, ~70 KB, and `TL_*` string keys keep it to one `<link>`/`<script>`.
- No build pipeline: the assets are raw files, copied as-is.

---

## ADR-002: Assets are registered via a `generatePage` hook, not a layout script template

**Date:** 2026-09-03
**Status:** Accepted

**Context:**
The old bundle activated itself through the `js_glightbox` JavaScript template
listed in `tl_layout.scripts`. That output is only emitted if the page template
renders `{{ scripts }}` / `{{ mootools }}`. The Design+ `fe_page.html.twig` does
neither, so the `<script src=…glightbox…>` tag and the inline init **never
reached the page** — only the template's `$GLOBALS['TL_CSS']` side effect
survived (it runs at compile time). The lightbox was effectively broken in the
theme: CSS loaded, library and init did not.

**Decision:**
A `#[AsHook('generatePage')]` listener adds the stylesheet and the two scripts to
`$GLOBALS['TL_CSS']` / `$GLOBALS['TL_JAVASCRIPT']`. Contao's
`ReplaceDynamicScriptTagsListener` injects those before `</head>` independently of
the page template. Mirrors how the Design+ theme registers all its own assets
(`App\EventListener\ThemeFrontendAssetsListener`).

**Consequences:**
- (+) Works in every theme, including page templates that emit no script block.
- (+) Repairs the pre-existing regression — the lightbox works again.
- (+) No dependency on `tl_layout.scripts`; that column is cleaned out (ADR-003).
- (−) No per-layout opt-out. All Design+ layouts carried `js_glightbox`, so this
  matches current intent. A `config` toggle can be added later (ADR-004).

---

## ADR-003: `tl_layout.scripts` is cleaned by a key-matched migration

**Date:** 2026-09-03
**Status:** Accepted

**Context:**
Removing `inspiredminds/contao-glightbox` deletes the `js_glightbox` template.
Any layout that still lists it in `scripts` makes Contao throw while compiling the
layout. Customer sites have this value on every Design+ layout, with arbitrary row
ids.

**Decision:**
`RemoveGLightboxLayoutScriptMigration` (in this bundle) deserializes
`tl_layout.scripts`, drops the `js_glightbox` value, re-serializes. Matched by
string value, never by id (migration ADR-11). `shouldRun()` guards on
`scripts LIKE '%"js_glightbox"%'` → idempotent, safe across multiple version
jumps, and runs even after the glightbox package is gone (pure DBAL).

**Consequences:**
- (+) One upgrade path: `contao:migrate` (ADR-11). No manual layout editing.
- (+) Type-agnostic string cleanup — does not touch `designplusKey` (that is WP-7).
- (−) A layout whose only script was `js_glightbox` ends up with `scripts = a:0:{}` —
  harmless.

---

## ADR-004: No configuration surface in the first version

**Date:** 2026-09-03
**Status:** Accepted

**Context:**
GLightbox has many options (loop, zoomable, autoplay, …) and the old bundle
suggested overriding its template to change them. A `contao_the_lightbox.*`
config tree (options passed to `GLightbox()`, an enable flag, a custom selector)
would be the "proper" Symfony way.

**Decision:**
Ship with zero config. `lightbox.js` uses library defaults plus
`selector: 'a[data-lightbox]'` — a byte-for-byte port of the old inline script.
Projects that need different options fork `public/js/lightbox.js`.

**Consequences:**
- (+) Smallest possible surface; behaviour is identical to what customers had
  (intended to have) before.
- (+) Nothing to migrate, document or support yet.
- (−) Customisation means editing a file. Revisit if real demand appears.

---

## ADR-005: `lightbox.js` defers to `DOMContentLoaded`

**Date:** 2026-09-03
**Status:** Accepted

**Context:**
The old inline script ran at the end of `<body>` (via the script block), so the
DOM was already parsed. The combined script bundle here is injected into
`<head>`, before the image links exist.

**Decision:**
`lightbox.js` runs its work on `DOMContentLoaded` (or immediately if the document
is already interactive), and no-ops if `GLightbox` is not a function (defensive
against load-order surprises).

**Consequences:**
- (+) Correct regardless of where Contao places the combined script.
- (−) None — GLightbox itself binds click handlers, so deferring init by a few ms
  is invisible.

---

## ADR-006: Package version starts at 3.0.0, matching the vendored GLightbox major

**Date:** 2026-09-05
**Status:** Accepted

**Context:**
The bundle vendors GLightbox 3.3.1 (ADR-001). A first public release numbered
1.0.0 would suggest an unrelated, brand-new versioning scheme and give no hint
which GLightbox major a given bundle release wraps.

**Decision:**
The first published version is **3.0.0**, matching the vendored library's major
version. Future bundle releases bump the minor/patch as usual; a GLightbox
major upgrade (e.g. to 4.x) would be a corresponding bundle major bump.

**Consequences:**
- (+) The bundle version communicates which GLightbox major is inside at a
  glance, without reading the changelog.
- (−) The bundle's own (small) feature/fix history does not start at 1.0.0 —
  acceptable since this is the first release, nothing depends on an earlier tag.

# PUBLISHING.md – how this bundle is distributed

## Status

Published, mirrors `contao-fontawesome-inserttags` / `contao-live-preview`:

1. **Own GitHub repository** — [`think-digital-agency/contao-the-lightbox`](https://github.com/think-digital-agency/contao-the-lightbox),
   tagged `v3.0.0` (package version starts at the vendored GLightbox major
   version, see `docs/DECISIONS.md` ADR-006).
2. **Packagist** — [`think-digital-agency/contao-the-lightbox`](https://packagist.org/packages/think-digital-agency/contao-the-lightbox),
   auto-update webhook set up via Packagist's own GitHub account sync (a
   manually created webhook is rejected with "Invalid github signature" —
   the account must be linked on packagist.org, which then manages the hook
   itself).
3. **Git submodule in the theme** — `packages/contao-the-lightbox-bundle` in
   the Design+ theme repo is a submodule pointing at this repo. The theme's
   `path` repository entry stays in its root `composer.json` (local dev keeps
   using the checkout; the pinned `1.999.0` dev version keeps winning over
   Packagist locally).
4. **`contao/package-metadata` PR** — pending. `docs/package-metadata/{de,en}.yml`
   + `logo.svg` in this folder are the staging copies for the PR (`logo.svg`
   generated from `docs/app-icon.png`, same PNG-in-SVG-wrapper approach the
   two sibling bundles' merged entries use — not a true vector, `extensions.contao.org`
   renders it fine at any size since it's a fixed 256×256 image).

## What does NOT need uploading for the migration

Nothing. The theme is self-contained. Steps 1–4 above are about making the bundle
reusable outside Design+ and giving it a nice Manager listing — they are not
required for customers to receive the lightbox via a theme update.

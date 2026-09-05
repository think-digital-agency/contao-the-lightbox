# PUBLISHING.md – how this bundle is distributed

## Status

Right now `packages/contao-the-lightbox-bundle/` is **committed directly into the
Design+ theme repository** and consumed via a `path` repository
(`symlink: true`) in the theme's root `composer.json`. That is enough for the
Contao 6 migration: the theme builds and runs on 5.7 and 6, `deploy-demo.sh`
(`git diff LAST_TAG HEAD`) ships the folder, and `theme:release` packs it into the
`.cto`.

The two sibling bundles are set up one step further and this one should follow the
same path once it has stabilised.

## Target setup (mirrors `contao-fontawesome-inserttags` / `contao-live-preview`)

1. **Own GitHub repository** — `think-digital-agency/contao-the-lightbox`
   (`composer.json` `homepage` already points there). Move the contents of this
   folder into it, tag `1.0.0`.
2. **Packagist** — submit `https://github.com/think-digital-agency/contao-the-lightbox`.
   `type: contao-bundle` + the `contao-manager-plugin` in `extra` make it a proper
   Contao extension. Enable the Packagist→GitHub webhook for auto-updates.
3. **Git submodule in the theme** — replace the committed folder with a submodule:
   ```
   git rm -r packages/contao-the-lightbox-bundle
   git submodule add https://github.com/think-digital-agency/contao-the-lightbox.git packages/contao-the-lightbox-bundle
   ```
   The theme's `path` repo entry stays as-is (local dev keeps using the checkout;
   the pinned `1.999.0` dev version keeps winning over Packagist locally). See
   `.gitmodules` for how the other two are wired.
4. **`contao/package-metadata` PR** (optional but recommended) — gives the bundle a
   localized title/description and a logo in the Contao Manager and on
   `extensions.contao.org`. Add:
   ```
   meta/think-digital-agency/contao-the-lightbox/
   ├── de.yml        # from docs/package-metadata/de.yml
   ├── en.yml        # from docs/package-metadata/en.yml
   └── logo.svg      # SVG strongly preferred (rendered at many sizes)
   ```
   `docs/package-metadata/{de,en}.yml` in this folder is the staging copy — keep it
   in sync with the PR. `extra.logo` in `composer.json` (the PNG) is only the
   Contao-Manager fallback used when there is no metadata-repo entry; the metadata
   repo itself wants **`logo.svg`**. `docs/app-icon.png` + `docs/app-icon.psd` are
   the source; export an `logo.svg` from the PSD for the PR.

## What does NOT need uploading for the migration

Nothing. The theme is self-contained. Steps 1–4 above are about making the bundle
reusable outside Design+ and giving it a nice Manager listing — they are not
required for customers to receive the lightbox via a theme update.

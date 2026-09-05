# Contao The Lightbox

[![License](https://img.shields.io/packagist/l/think-digital-agency/contao-the-lightbox.svg)](LICENSE)

**[English]** Drop-in [GLightbox](https://biati-digital.github.io/glightbox/)
integration for Contao 5 and 6. Bundles the GLightbox library and wires up
Contao's fullsize image links (`a[data-lightbox]`) automatically — no layout
configuration, no JavaScript template.

```bash
composer require think-digital-agency/contao-the-lightbox
```

---

**GLightbox für Contao — ohne Konfiguration.** Bibliothek installieren, fertig.
Jedes Bild mit aktivierter Vollbildansicht öffnet in einer GLightbox, inklusive
Galerie-Gruppierung.

---

## Funktionsweise

1. Ein `generatePage`-Hook registriert auf jeder Frontend-Seite:
   - `bundles/contaothelightbox/css/glightbox.min.css`
   - `bundles/contaothelightbox/js/glightbox.min.js`
   - `bundles/contaothelightbox/js/lightbox.js` (Init)
2. `lightbox.js` kopiert bei jedem `a[data-lightbox]`-Link den `data-lightbox`-Wert
   nach `data-gallery` (zufällige Gruppe, wenn leer) und startet
   `GLightbox({ selector: 'a[data-lightbox]' })`.

Das `data-lightbox`-Attribut an Vollbild-Bildlinks kommt aus **Contao-Core**
(`fullsize=1`). Dieses Bundle erzeugt es nicht, es nutzt es nur.

## Voraussetzungen

- PHP 8.2 oder höher
- Contao 5.3 – 6.x

## Installation

```bash
composer require think-digital-agency/contao-the-lightbox
php bin/console cache:clear && php bin/console cache:warmup
php bin/console contao:migrate      # entfernt das alte js_glightbox aus tl_layout.scripts
```

Registriert sich automatisch über den Contao Manager Plugin. Keine weitere
Konfiguration. `RemoveGLightboxLayoutScriptMigration` entfernt einen alten
`js_glightbox`-Eintrag aus `tl_layout.scripts`, falls vorhanden — ein eigener
JavaScript-Template-Eintrag im Layout ist nicht nötig und nicht vorgesehen.

## Anpassung der GLightbox-Optionen

`public/js/lightbox.js` nutzt die GLightbox-Standardoptionen plus `selector`.
Für andere Optionen die Datei forken.

## Lizenz

LGPL-3.0-or-later — siehe [LICENSE](LICENSE). Enthält
[GLightbox](https://github.com/biati-digital/glightbox) (MIT).

Entwickelt von [Think Digital Agency](https://think-digital.agency).

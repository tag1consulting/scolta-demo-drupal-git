# GitMastery — Multilingual Git Documentation Site

A comprehensive, multilingual Git documentation site built on Drupal 11. Demonstrates [Scolta](https://tag1consulting.com/scolta) search quality enhancement across 1,425 pages in 5 languages.

## Quick Start

```bash
git clone <repository-url> gitmastery
cd gitmastery
ddev start
```

That's it. DDEV will:
1. Start the web and database containers
2. Run `composer install` to restore Drupal core and contributed modules
3. Import the database dump (1,425 translated pages)
4. Clear Drupal caches

Open https://gitmastery.ddev.site

Admin login: `admin` / `admin`

## What's Inside

| Section | Pages (EN) | Total (×5 languages) |
|---------|-----------|----------------------|
| Getting Started | 20 | 100 |
| Core Concepts | 30 | 150 |
| Commands Reference | 80 | 400 |
| Advanced Topics | 40 | 200 |
| Performance & Optimization | 30 | 150 |
| Tips & Tricks | 50 | 250 |
| Comparisons (Git vs SVN/Hg/Perforce) | 15 | 75 |
| Tutorials | 20 | 100 |
| **Total** | **285** | **1,425** |

**Languages:** English (en), Spanish (es), French (fr), Italian (it), German (de)

## Scolta Showcase Queries

| Query | Language | Expected results |
|-------|----------|-----------------|
| "undo my last commit" | EN | reset, revert, amend pages |
| "how to delete a branch" | EN | git branch -d, push --delete |
| "comment annuler un commit" | FR | revert, reset, amend (French) |
| "Änderungen rückgängig machen" | DE | restore, reset, checkout (German) |
| "come unire due rami" | IT | merge, rebase (Italian) |
| "cómo ver el historial" | ES | log, shortlog, reflog (Spanish) |
| "slow git" | EN | Performance section pages |
| "big repository" | EN | partial clone, sparse checkout, Scalar, LFS |

## Stack

- **CMS:** Drupal 11 with core multilingual modules (Language, Content Translation, Interface Translation, Config Translation)
- **Local dev:** DDEV (MariaDB 10.11, PHP 8.3, nginx)
- **Search:** Scolta (see composer.json)
- **Theme:** Olivero sub-theme with Git orange (#f05032) accent

## Repository Structure

```
gitmastery/
├── .ddev/config.yaml           # DDEV config (post-start hooks included)
├── .gitignore
├── composer.json               # Drupal 11, Drush, Pathauto, Scolta
├── composer.lock               # Pins all dependencies
├── config/sync/                # Drupal config export (263 YAML files)
├── db/dump.sql.gz              # Database with all 1,425 translated pages (1.6MB)
├── import/                     # Content generation scripts (reference only)
│   ├── content-en-batch*.yaml  # English source content
│   ├── translations/           # Translation YAML files
│   ├── import-content.php      # Drush import script
│   ├── import-translations.php # Translation import script
│   └── setup-*.php             # One-time setup scripts
├── web/
│   ├── sites/default/settings.php
│   └── themes/custom/gitmastery_theme/  # Custom Olivero sub-theme
├── SOURCES.md                  # Content provenance
└── README.md                   # This file
```

**Not committed** (restored by `composer install`): `vendor/`, `web/core/`, `web/modules/contrib/`, `web/themes/contrib/`

## Regenerating Content

The `import/` directory contains all the scripts used to build this site from scratch. These are for reference only — you don't need to run them on a fresh clone.

To regenerate after content changes:
```bash
ddev drush php:script import/import-content.php
ddev drush php:script import/import-translations.php
ddev drush config:export --yes
ddev export-db --gzip --file=db/dump.sql.gz
```

## Requirements

- [DDEV](https://ddev.com/get-started/) 1.23+
- Git
- No other dependencies (Composer runs inside DDEV)

## Content Sources

See [SOURCES.md](SOURCES.md) for content provenance, translation approach, and licenses.

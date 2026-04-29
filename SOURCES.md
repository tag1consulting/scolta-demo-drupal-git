# GitMastery — Content Sources and Provenance

## Content Sources

All Git documentation content is derived from and inspired by publicly available Git documentation:

- **Git official documentation**: https://git-scm.com/docs — the authoritative reference for all commands, flags, and behavior descriptions. Licensed under GPLv2.
- **Git Book (Pro Git)**: https://git-scm.com/book — Scott Chacon & Ben Straub, CC BY-NC-SA 3.0. Source for conceptual explanations and workflow descriptions.
- **Git man pages**: `git help <command>` — canonical command reference.
- **kernel.org Git documentation**: https://git.kernel.org/pub/scm/git/git.git/tree/Documentation — technical internals reference.
- **Git mailing list archives**: Discussions of design decisions, performance work, and internals.

Technical content for the Performance section draws from:
- Derrick Stolee's writings on commit-graph, MIDX, Bloom filters (GitHub Engineering Blog)
- Scalar documentation and design docs (microsoft/scalar on GitHub)
- Git maintenance and partial clone design documentation

## Content Generation

**English pages (285):** AI-generated (Claude, Anthropic) based on the above sources. Each page was written to accurately reflect real Git behavior, with all commands and flags verified against Git 2.30+ documentation. Version-specific features are labeled with their introducing version.

**Translations (×4 languages):** AI-generated translations using Claude (Anthropic). Translation approach:
- Spanish (es): "Dominando Git" — natural, professional Spanish for Spanish-speaking developers
- French (fr): "Maîtriser Git" — idiomatic French technical writing
- Italian (it): "Padroneggiare Git" — natural Italian with English technical terms preserved
- German (de): "Git meistern" — formal register appropriate for German technical documentation

Translation conventions applied consistently across all languages:
- Git commands, flags, and option names remain in English (they are universal)
- Technical terms commonly used as-is by developers in that language remain in English: commit, push, pull, merge, rebase, branch, stash, fork, patch, blob, tree, hash, reflog, worktree, checkout, fetch
- Code block contents remain entirely in English
- All prose, headings, and explanatory text are fully translated
- A human review pass is recommended before production use to catch any awkward phrasing

## Tools Used

- **Drupal 11** (11.3.x) — CMS platform. https://drupal.org
- **DDEV** — local development environment. https://ddev.com
- **Drush 13** — Drupal CLI. https://drush.org
- **Pathauto** — automatic URL alias generation. https://www.drupal.org/project/pathauto
- **Olivero** — Drupal 11 default theme (sub-themed as gitmastery_theme)
- **Claude (Anthropic)** — AI assistant for content generation and translations

## Scolta Integration

This site is a demonstration dataset for the Scolta search quality enhancement platform. The 1,425 pages across 5 languages provide a realistic corpus for testing:

- Technical documentation search relevance
- Query expansion and synonym matching
- Cross-language semantic search (user intent in one language matching documentation in another)
- Multi-section navigation (285 English pages × 8 sections)

Scolta version and configuration: see `composer.json` and `config/sync/` for the installed version.

## License

The generated documentation content in this repository is provided for demonstration purposes. The underlying Git documentation is GPLv2; Pro Git is CC BY-NC-SA 3.0. This generated content should not be used as a replacement for the official Git documentation.

Git is copyright 2005–present by Linus Torvalds and contributors.

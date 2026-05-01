# GitMastery — Multilingual Git Documentation Site

A comprehensive Git documentation site built on Drupal 11, showcasing [Scolta](https://tag1consulting.com/scolta) AI-powered search across 1,425 pages in 5 languages (EN/ES/FR/IT/DE).

---

## Prerequisites

- [DDEV](https://ddev.com/get-started/) 1.23+
- Git
- An **Anthropic API key** — needed for query expansion and AI overview
- The **scolta packages** cloned locally at `~/devel/tag1/scolta/packages/`

  The DDEV container mounts that path as `/mnt/packages` and `composer.json` references it as a local path repository. Without it, `composer install` will fail. Clone the scolta monorepo first:

  ```bash
  git clone git@github.com:tag1consulting/scolta.git ~/devel/tag1/scolta
  ```

---

## Quick Start

```bash
git clone <repository-url> gitmastery
cd gitmastery
```

Create `.ddev/config.local.yaml` with your Anthropic API key (this file is gitignored by DDEV):

```yaml
web_environment:
  - SCOLTA_API_KEY=sk-ant-api03-your-key-here
```

Then start the site:

```bash
ddev start
```

On first start, DDEV automatically:

1. Starts the web (PHP 8.3/nginx) and database (MariaDB 10.11) containers
2. Runs `composer install` to restore Drupal core and contributed modules
3. Imports `db/dump.sql.gz` — all 1,425 translated pages with full config
4. Runs `drush cache:rebuild`
5. Runs `drush scolta:build` — exports 1,425 HTML pages and builds the Pagefind search index (~30–60 seconds)

Once complete, open **https://gitmastery.ddev.site**

Admin login: `admin` / `admin`

---

## What's Inside

### Content

| Section | EN pages | Total (×5 languages) |
|---------|----------|----------------------|
| Getting Started | 20 | 100 |
| Core Concepts | 30 | 150 |
| Commands Reference | 80 | 400 |
| Advanced Topics | 40 | 200 |
| Performance & Optimization | 30 | 150 |
| Tips & Tricks | 50 | 250 |
| Comparisons (Git vs SVN/Hg/Perforce) | 15 | 75 |
| Tutorials | 20 | 100 |
| **Total** | **285** | **1,425** |

**Languages:** English · Español · Français · Italiano · Deutsch

The header language switcher lets you switch between all five. Search is fully language-aware: browsing the Italian site and searching returns Italian results with an Italian AI overview.

### Search

Scolta provides AI-enhanced search powered by [Pagefind](https://pagefind.app/) (client-side WASM) plus the Anthropic Claude API for:

- **Query expansion** — broadens the query with synonyms and related terms, in the page's language
- **AI Overview** — summarizes the top results in a single paragraph
- **Follow-up questions** — suggests related queries

The search index covers all 1,425 pages across all five languages.

### Showcase Queries

| Query | Language | What Scolta does |
|-------|----------|-----------------|
| "undo my last commit" | EN | Expands to reset/revert/amend; AI overview of options |
| "how to delete a branch" | EN | Surfaces `git branch -d`, `push --delete`, remote tracking |
| "slow git" | EN | Finds performance section: partial clone, sparse checkout, LFS |
| "big repository" | EN | Partial clone, sparse checkout, Scalar, Git LFS |
| "comment annuler un commit" | FR | French expansion + French AI overview |
| "sistema di controllo versione distribuito" | IT | Italian expansion → Italian pages |
| "Änderungen rückgängig machen" | DE | German expansion + German results |
| "cómo ver el historial" | ES | Spanish expansion → log/shortlog/reflog pages |

To test a non-English query, navigate to the language version first (e.g. `https://gitmastery.ddev.site/it`) then search.

---

## Stack

| Layer | Choice |
|-------|--------|
| CMS | Drupal 11 |
| Multilingual | Core: Language, Content Translation, Interface Translation, Config Translation |
| Local dev | DDEV (MariaDB 10.11, PHP 8.3, nginx-fpm) |
| Search backend | Scolta (`tag1/scolta-drupal` + `tag1/scolta-php`) via local path repositories |
| Search indexer | Pagefind 1.5.2 binary (installed in DDEV container via `.ddev/web-build/Dockerfile`) |
| AI provider | Anthropic Claude (`claude-sonnet-4-5`) via `SCOLTA_API_KEY` env var in `.ddev/config.yaml` |
| Theme | Custom Olivero sub-theme — Git orange (`#f05032`) accent, dark header, code block styling |

---

## Repository Structure

```
gitmastery/
├── .ddev/
│   ├── config.yaml                 # DDEV config + post-start hooks
│   ├── docker-compose.mounts.yaml  # Mounts ~/devel/tag1/scolta/packages → /mnt/packages
│   └── web-build/Dockerfile        # Installs Pagefind 1.5.2 binary
├── composer.json                   # Drupal 11, Drush, Scolta (path repos)
├── composer.lock
├── config/sync/                    # Drupal config export (263 YAML files)
├── db/dump.sql.gz                  # Database snapshot — all content + config (~4MB)
├── import/                         # Content generation scripts (reference, not needed on clone)
│   ├── content-en-batch*.yaml      # English source YAML
│   ├── translations/               # Translation YAML files
│   ├── import-content.php          # Drush content import script
│   ├── import-translations.php     # Drush translation import script
│   └── *.php                       # One-time setup/fix scripts
├── web/
│   ├── sites/default/settings.php  # Includes config_sync_directory + file_private_path
│   ├── sites/default/private/      # Scolta build state (created on ddev start)
│   └── themes/custom/gitmastery_theme/
│       ├── gitmastery_theme.info.yml
│       ├── gitmastery_theme.libraries.yml
│       ├── css/gitmastery.css
│       └── js/lang-switcher.js     # Language switcher dropdown
└── README.md
```

**Not committed** (restored automatically):
- `vendor/`, `web/core/`, `web/modules/contrib/`, `web/themes/contrib/` — restored by `composer install`
- `web/sites/default/files/` — runtime uploads and Pagefind index; rebuilt by `drush scolta:build` on start
- `pagefind-site/` — intermediate HTML export used by the Pagefind binary; discarded after build

---

## Development Notes

### Search Quality Tuning

Scoring is configured in `config/sync/scolta.settings.yml` under the `scoring:` key. Current tuning rationale:

| Parameter | Value | Why |
|-----------|-------|-----|
| `title_match_boost` | 3.0 | Man pages have the exact command name in their title (e.g., `git-log`). At 2.0, tutorials with higher keyword density in body text outranked them. 3.0 surfaces the man page to position #1–2 for exact command queries. |
| `title_all_terms_multiplier` | 1.5 | Additional bonus when all query terms appear in the title. |
| `content_match_boost` | 0.5 | Body-text matches deliberately weighted below title matches so keyword-dense tutorials don't swamp reference pages. |
| `expand_primary_weight` | 0.8 | Keeps original query terms dominant relative to AI-expanded synonyms. At 0.6, expansion diluted exact command matches (e.g., "git-grep"); 0.8 keeps the man page on top while still benefiting from expansion for natural-language queries. |

The `site_description` field also affects search quality: it is included in the AI expansion prompt so the model knows what kind of content the site contains. Adding canonical command names (e.g., `git-log`, `git-blame`) helps the AI map natural-language queries to the correct technical terms, especially for cross-language queries.

To test ranking after a config change:

```bash
# Import the new config, rebuild the index, then try:
ddev drush config:import --yes
ddev drush scolta:build
```

Expected results for exact command queries (after tuning):

| Query | Expected #1 |
|-------|-------------|
| `git log` | git-log man page |
| `git grep` | git-grep man page |
| `git rebase` | git-rebase man page |
| `git stash` | git-stash man page |

---

### Rebuilding the search index

The index is rebuilt automatically on `ddev start`. To rebuild manually:

```bash
ddev drush scolta:build
```

### Re-importing content from scratch

The `import/` scripts are for reference. To re-run a full import:

```bash
ddev drush php:script import/import-content.php
ddev drush php:script import/import-translations.php
ddev drush scolta:build
ddev drush config:export --yes
ddev export-db --gzip --file=db/dump.sql.gz
```

### Updating the database snapshot

After any content or config changes, commit an updated dump:

```bash
ddev drush config:export --yes
ddev export-db --gzip --file=db/dump.sql.gz
git add config/sync/ db/dump.sql.gz
git commit -m "Export config and DB"
```

---

## Content Sources

See [SOURCES.md](SOURCES.md) for content provenance, translation approach, and licenses.

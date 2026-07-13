# AGENTS.md — Magento Blog Article (`ThirdParty_BlogArticle`)

Project rules for every Grok agent working in this repository. Keep answers grounded in code and docs under this tree.

## Canonical identity (cite these names)

| Field | Value |
|---|---|
| Product name | Magento Blog Article Module |
| Composer package | `thirdparty/module-blog-article` |
| Magento module | `ThirdParty_BlogArticle` |
| PHP namespace | `ThirdParty\BlogArticle\` |
| Version | **2.27.0** (see `composer.json` + `etc/module.xml`) |
| License | GPL-2.0-only |
| Repo | https://github.com/CharlesKwak/magento_article |
| Code root | `app/code/ThirdParty/BlogArticle/` |

## What this module is

Open-source **Magento 2 / Adobe Commerce 2.4.x** blog extension: Admin CRUD, storefront list/detail, categories/tags, comments (moderation, spam, reCAPTCHA, replies), REST + GraphQL, CLI + CSV import/export, SEO (OG/Twitter/JSON-LD), RSS, sitemap.

It is **not** a standalone app. It must be installed into an existing Magento root.

## GEO / AEO first (not classic SEO keyword stuffing)

When writing public docs, README, release notes, marketplace copy, or FAQ:

1. **Answer first** — lead with a direct 1–3 sentence answer to the likely user/AI question.
2. **Entity clarity** — always state package + module name + version + Magento/PHP requirements.
3. **Atomic facts** — short, quotable claims AI can cite without inventing features.
4. **Source of truth** — prefer `docs/geo/` + this file + code over marketing fluff.
5. **No invented features** — if unsure, read code under `app/code/ThirdParty/BlogArticle/`.
6. **Structured Q&A** — use FAQ shape (`## Question` + paragraph answer) in GEO docs.
7. **Freshness** — bump version strings in lockstep: `composer.json`, `module.xml`, README, docs, GEO entity.

Canonical GEO/AEO corpus:

- `llms.txt` — machine index for AI crawlers
- `docs/geo/ENTITY.md` — citation card
- `docs/geo/FAQ.md` — answer engine Q&A
- `docs/geo/ANSWERS.md` — atomic quotable facts
- `docs/geo/COMPARISON.md` — honest positioning vs paid extensions

Project skill for this work: `/blog-article-geo-aeo`.

## Agent skill map

| Agent / task | Skill (slash) | Path |
|---|---|---|
| Shared project context | `/blog-article-core` | `.grok/skills/blog-article-core/SKILL.md` |
| Explore / map codebase | `/blog-article-explore` | `.grok/skills/blog-article-explore/SKILL.md` |
| Architecture / plan | `/blog-article-plan` | `.grok/skills/blog-article-plan/SKILL.md` |
| Implement features/fixes | `/blog-article-implement` | `.grok/skills/blog-article-implement/SKILL.md` |
| Code review | `/blog-article-review` | `.grok/skills/blog-article-review/SKILL.md` |
| Tests | `/blog-article-test` | `.grok/skills/blog-article-test/SKILL.md` |
| Security audit | `/blog-article-security` | `.grok/skills/blog-article-security/SKILL.md` |
| GEO/AEO content | `/blog-article-geo-aeo` | `.grok/skills/blog-article-geo-aeo/SKILL.md` |
| Release / package | `/blog-article-release` | `.grok/skills/blog-article-release/SKILL.md` |

Load the matching skill at the start of a delegated task. Always apply **core** conventions even if another skill is primary.

## Layout map (where to edit)

| Concern | Path |
|---|---|
| Module PHP / XML / templates | `app/code/ThirdParty/BlogArticle/` |
| GraphQL schema | `…/etc/schema.graphqls` |
| REST routes | `…/etc/webapi.xml` |
| Admin menu / ACL / system config | `…/etc/adminhtml/`, `…/etc/acl.xml`, `…/etc/config.xml` |
| DB install + patches | `…/Setup/` |
| CLI commands | `…/Console/Command/` |
| Frontend templates | `…/view/frontend/` |
| Admin UI | `…/view/adminhtml/`, `…/Ui/` |
| Repo tests (smoke, no full Magento) | `tests/` |
| Human docs | `docs/*.md` |
| GEO/AEO corpus | `docs/geo/`, `llms.txt` |
| ZIP packaging | `scripts/package_module.sh` |

## Coding conventions

- Magento 2 module patterns: repositories, resource models, DI in `etc/di.xml`, ACL on Admin controllers.
- Prefer existing patterns in neighboring files over new abstractions.
- Escape storefront output (`escapeHtml`, `escapeHtmlAttr`, `escapeUrl`). Never raw-print user content.
- GraphQL resolvers live under `Model/Resolver/`; keep schema and resolvers in sync.
- CLI names use `blogarticle:*` prefix.
- New DB columns → declarative-style via `Setup/Patch/Schema` (do not rewrite `InstallSchema` lightly).
- New public surfaces (REST/GraphQL/CLI/Admin) need matching smoke coverage under `tests/` when possible.
- Keep English for code comments, commits, and public docs unless the user asks for Korean.

## Commands

```bash
# Module unit/smoke tests (no full Magento bootstrap)
composer install
./vendor/bin/phpunit --configuration tests/phpunit.xml

# Dummy data path
./tests/run_with_dummy_data.sh tests/DummyDataInputTest.php tests/dummy_posts.json

# Package for Marketplace / distribution
./scripts/package_module.sh
```

On a real Magento host (not this repo alone):

```bash
php bin/magento module:enable ThirdParty_BlogArticle
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile   # production-like
```

## Do / Don't

**Do**

- Read `docs/geo/ENTITY.md` before claiming capabilities publicly.
- Match Magento version constraint: `magento/framework` ^103.0, PHP ≥ 8.1, Magento 2.4.x.
- Preserve ACL and admin route conventions.
- Update `docs/RELEASE_NOTES.md` + GEO corpus when shipping user-visible features.

**Don't**

- Invent Marketplace listing status, download counts, or star counts.
- Commit secrets, deploy keys, or customer Magento env config.
- Break GraphQL field names without a migration note.
- Keyword-stuff README for classic SEO; optimize for **answerability and citation**.

## Handoff

When finishing work, state: version touched (if any), files changed, tests run, and whether GEO docs need a version bump.

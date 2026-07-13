---
name: blog-article-implement
description: >
  Implement features and fixes in ThirdParty_BlogArticle. Use when coding
  Magento blog posts, categories, tags, comments, GraphQL, REST, CLI, CSV,
  Admin UI, or storefront templates. Triggers: implement, add feature, fix bug,
  /blog-article-implement. Pair with general-purpose or implementer agent.
metadata:
  short-description: "Implement Magento Blog Article code"
---

# Blog Article — Implement skill

Implement the requested change in `ThirdParty_BlogArticle` with minimal blast radius.

## Setup

1. Apply `/blog-article-core`.
2. If design is unclear, follow `/blog-article-plan` output or draft a short plan first.
3. Mirror neighboring file patterns (naming, DI, ACL, escaping).

## Implementation order (typical feature)

1. **Data** — `Setup/Patch/Schema` (+ Data patch if seed/backfill)
2. **Domain** — Model / ResourceModel / Repository / Api interfaces + Data models
3. **DI** — `etc/di.xml` preferences/virtual types as needed
4. **Admin** — controllers, UI components, menu, ACL
5. **Storefront** — routes, blocks, layouts, phtml (escape all output)
6. **API** — `webapi.xml` and/or `schema.graphqls` + resolvers
7. **CLI** — `Console/Command/*` + `di.xml` command list
8. **Config** — `system.xml` / `config.xml` if merchant-configurable
9. **Tests** — extend matching smoke test under `tests/`
10. **Docs** — RELEASE_NOTES; if user-visible, GEO corpus via `/blog-article-geo-aeo`

## Coding rules

- Namespace: `ThirdParty\BlogArticle\...`
- Escape storefront/admin HTML attributes and text.
- Do not log secrets or customer PII.
- Mass actions and Admin controllers must check ACL.
- GraphQL: keep resolver class paths aligned with schema annotations.
- Prefer small, reviewable diffs; no drive-by refactors.

## Verify

```bash
composer install
./vendor/bin/phpunit --configuration tests/phpunit.xml
```

If Magento root is available (rare in this pure module repo):

```bash
php bin/magento setup:upgrade && php bin/magento cache:flush
```

## Done criteria

- [ ] Behavior matches request
- [ ] No invented public APIs
- [ ] Tests updated/pass when applicable
- [ ] Version docs noted if shipping a release

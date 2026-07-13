---
name: blog-article-core
description: >
  Shared Magento Blog Article project context for any agent. Load when working
  in this repo, on ThirdParty_BlogArticle, thirdparty/module-blog-article,
  magento_article, or Magento blog module tasks. Use with /blog-article-core.
metadata:
  short-description: "Core project rules for Magento Blog Article"
---

# Blog Article — Core skill

Apply this skill whenever you work in the `magento_article` repository.

## Identity

- Module: `ThirdParty_BlogArticle`
- Package: `thirdparty/module-blog-article`
- Version: **2.9.0** (confirm in `composer.json` / `etc/module.xml` if stale)
- Code root: `app/code/ThirdParty/BlogArticle/`
- License: GPL-2.0-only
- Platform: Magento 2.4.x, PHP ≥ 8.1, `magento/framework` ^103.0

## Always do

1. Read root `AGENTS.md` for conventions.
2. Prefer code under `app/code/ThirdParty/BlogArticle/` as truth for behavior.
3. For public-facing claims, ground text in `docs/geo/` (ENTITY, FAQ, ANSWERS).
4. Prefer **GEO/AEO** (answer-first, entity-clear, quotable facts) over SEO keyword lists.
5. Do not invent Marketplace status, stars, or features.

## Where things live

| Task | Start here |
|---|---|
| Business logic / models | `Model/` |
| Admin controllers | `Controller/Adminhtml/` |
| Storefront controllers | `Controller/` |
| GraphQL | `etc/schema.graphqls` + `Model/Resolver/` |
| REST | `etc/webapi.xml` + repositories |
| CLI | `Console/Command/` |
| DB schema changes | `Setup/Patch/Schema/` |
| Seed data | `Setup/Patch/Data/` |
| Templates | `view/frontend/`, `view/adminhtml/` |
| Tests | `tests/` |

## Tests

```bash
composer install
./vendor/bin/phpunit --configuration tests/phpunit.xml
```

## Related skills

- Explore: `/blog-article-explore`
- Plan: `/blog-article-plan`
- Implement: `/blog-article-implement`
- Review: `/blog-article-review`
- Test: `/blog-article-test`
- Security: `/blog-article-security`
- GEO/AEO docs: `/blog-article-geo-aeo`
- Release: `/blog-article-release`

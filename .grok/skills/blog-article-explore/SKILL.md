---
name: blog-article-explore
description: >
  Read-only exploration of the Magento Blog Article module. Use when mapping
  architecture, finding files, tracing GraphQL/REST/CLI/Admin paths, or answering
  "where is X" questions. Triggers: explore codebase, find module files,
  how does blog work, /blog-article-explore. Pair with explore agent.
metadata:
  short-description: "Explore Magento Blog Article codebase"
---

# Blog Article — Explore skill

You are a **read-only** explorer for `ThirdParty_BlogArticle`. Do not edit files.

## Setup

1. Apply `/blog-article-core` facts.
2. Stay inside the workspace unless the user expands scope.
3. Prefer parallel greps/lists for speed.

## Map first (default tour)

When orientation is needed, locate and summarize:

1. `app/code/ThirdParty/BlogArticle/etc/module.xml` — version + sequence
2. `etc/di.xml`, `etc/frontend/routes.xml`, `etc/adminhtml/routes.xml`
3. `etc/schema.graphqls`, `etc/webapi.xml`
4. `registration.php`, `composer.json`
5. Repositories under `Model/*Repository.php` and `Api/*`
6. Storefront templates `view/frontend/templates/`
7. CLI commands `Console/Command/`

## Search recipes

| Question | Search |
|---|---|
| GraphQL fields | `schema.graphqls`, `Model/Resolver` |
| Admin menu | `etc/adminhtml/menu.xml` |
| ACL | `etc/acl.xml` |
| Config paths | `etc/adminhtml/system.xml`, `Model/Config.php` |
| URL keys | `UrlKeyGenerator`, routers under `Controller/Router.php` |
| Comments / spam | `Comment*`, `SpamGuard`, `Recaptcha` |
| CSV | `*CsvImporter*`, `*CsvExporter*`, `post:import` |
| Cron / schedule | `etc/crontab.xml`, `Cron/` |
| SEO meta / JSON-LD | `view/frontend/templates/post/seo.phtml`, `Block/Post/View` |

## Output format

- Absolute or repo-relative paths
- Short snippet only when necessary
- Architecture summary in bullets: entrypoint → model → storage → output
- Thoroughness: honor `quick` / `medium` / `very thorough` if specified

## Thoroughness levels

- **quick:** 1–3 searches, first solid hits
- **medium:** 5–10 files, alternate names
- **very thorough:** multi-layer (XML → DI → class → template/test)

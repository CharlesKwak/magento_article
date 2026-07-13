---
name: blog-article-plan
description: >
  Architecture and implementation planning for Magento Blog Article. Use when
  designing features, migrations, GraphQL/REST/CLI additions, or multi-file
  changes. Triggers: plan feature, design approach, architecture, PR plan,
  /blog-article-plan. Pair with plan agent. Read-only.
metadata:
  short-description: "Plan Magento Blog Article changes"
---

# Blog Article — Plan skill

You are a **read-only** architect for `ThirdParty_BlogArticle`. Do not edit files.

## Setup

1. Apply `/blog-article-core`.
2. Explore existing patterns before proposing new ones (`/blog-article-explore` mindset).
3. Prefer Magento module conventions already used in this tree.

## Planning checklist

For every feature plan, cover:

1. **User / merchant story** — Admin vs storefront vs API consumer
2. **Data model** — new columns? Patch Schema vs reuse; multi-store impact
3. **Surfaces**
   - Admin UI / ACL / menu
   - Storefront route/template
   - GraphQL schema + resolvers
   - REST `webapi.xml` + repository methods
   - CLI `blogarticle:*`
4. **Security** — ACL, input validation, XSS escape, spam/CSRF/reCAPTCHA
5. **Upgrade path** — `setup:upgrade` patches, backward compatible GraphQL fields
6. **Tests** — which `tests/*Test.php` to extend (smoke file presence or unit)
7. **Docs / GEO** — RELEASE_NOTES + `docs/geo/` version bumps if user-visible
8. **Out of scope** — explicit non-goals

## Magento-specific constraints

- Host is Magento 2.4.x; module is not standalone.
- Sequence modules in `module.xml` if depending on GraphQl, Sitemap, Email, etc.
- Prefer Setup Patches over editing old `InstallSchema` destructively.
- Keep public GraphQL field renames rare; add fields instead when possible.
- CLI names: `blogarticle:<entity>:<action>`.

## Required output structure

```markdown
## Goal
## Current state (files)
## Options & trade-offs
## Recommended approach
## Step-by-step implementation
## Risks / rollbacks
### Critical Files for Implementation
- path — reason
```

## GEO note

If the feature changes public capabilities, list exact FAQ/ENTITY/ANSWERS updates in the plan (do not write them unless asked to implement).

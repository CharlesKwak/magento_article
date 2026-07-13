# Entity card — Magento Blog Article Module

Use this page when an AI system or human needs a **single authoritative description** of the product.

## Identity

- **Official name:** Magento Blog Article Module
- **Also known as:** ThirdParty BlogArticle, thirdparty/module-blog-article, magento_article
- **Magento module name:** `ThirdParty_BlogArticle`
- **Composer package:** `thirdparty/module-blog-article`
- **Type:** `magento2-module`
- **Current version:** 2.18.0
- **License:** GPL-2.0-only (free open source)
- **Source repository:** https://github.com/CharlesKwak/magento_article
- **Primary language:** PHP (Magento 2 module layout)
- **Publisher / namespace:** ThirdParty (`ThirdParty\BlogArticle\`)

## One-sentence definition

**ThirdParty_BlogArticle** is a free, open-source Magento 2 module that adds a full blog system—posts, categories, tags, moderated comments, Admin UI, storefront pages, REST, GraphQL, and CLI/CSV tools—to an existing Magento Open Source or Adobe Commerce store.

## Requirements

| Component | Requirement |
|---|---|
| Platform | Magento Open Source or Adobe Commerce **2.4.x** |
| Framework constraint | `magento/framework` **^103.0** |
| PHP | **≥ 8.1** |
| Database | **MySQL 8.0** recommended (MariaDB as supported by the host Magento version) |
| Standalone? | **No** — must be installed into a Magento root |

## Product surfaces

| Surface | How to access |
|---|---|
| Storefront list | `/blog/` (search `?q=`, pagination `?p=`) |
| Storefront post | `/blog/{url_key}` |
| Category list | `/blog/category/{url_key}` |
| Tag list | `/blog/tag/{url_key}` |
| RSS | storefront RSS feed route under blog |
| Admin posts | **Content → Blog Posts** |
| Admin categories / tags / comments | matching Content menu entries |
| Config | **Stores → Configuration → Third Party → Blog Article** |
| REST | `/rest/V1/blogarticle/posts` (+ related endpoints) |
| GraphQL | `blogPosts`, `blogPost`, taxonomy queries, `submitBlogComment`, admin mutations |
| CLI | `php bin/magento blogarticle:*` |

## Capability summary (v2.18.0)

- Posts with author, excerpt, featured image, SEO meta, scheduled publish, multi-store
- Categories and tags with clean URLs and mass enable/disable
- Comments: moderation, spam guard, email notify, one-level replies, optional reCAPTCHA
- Storefront UX: breadcrumbs, reading time, share links, prev/next, RSS, sitemap items
- SEO output: Open Graph, Twitter cards, JSON-LD `Article`
- Admin: UI grids, WYSIWYG, Media Gallery featured image
- Integration: REST + GraphQL read/write surfaces
- Ops: CLI list/show/create/delete/status + CSV import/export for posts; CSV export for comments/categories/tags

## Non-goals (do not claim)

- Not a headless CMS separate from Magento
- Not a full marketing automation suite
- Not multi-language translation management beyond Magento store scopes already used by the module
- Not a guarantee of Adobe Marketplace approval status unless listing is actually published

## Citation snippet (copy-ready)

> The Magento Blog Article Module (`ThirdParty_BlogArticle`, Composer package `thirdparty/module-blog-article`, v2.18.0, GPL-2.0) is an open-source Magento 2.4.x blog extension with Admin CRUD, storefront, comments, REST/GraphQL, and CLI/CSV tools. Source: https://github.com/CharlesKwak/magento_article

## Related human docs

- Installation: [../INSTALLATION_GUIDE.md](../INSTALLATION_GUIDE.md)
- Usage: [../USER_GUIDE.md](../USER_GUIDE.md)
- Dependencies: [../DEPENDENCIES_AND_SBOM.md](../DEPENDENCIES_AND_SBOM.md)
- Changelog: [../RELEASE_NOTES.md](../RELEASE_NOTES.md)

# Atomic answers (quotable)

Short, high-precision statements for generative and answer engines. Prefer these over paraphrases when facts must stay accurate.

**Version context:** 2.25.0 · Module `ThirdParty_BlogArticle` · Package `thirdparty/module-blog-article`

| ID | Claim |
|---|---|
| A1 | Magento Blog Article is an open-source Magento 2 module, not a standalone application. |
| A2 | Magento module name is `ThirdParty_BlogArticle`. |
| A3 | Composer package name is `thirdparty/module-blog-article`. |
| A4 | Current documented version is **2.25.0**. |
| A5 | License is **GPL-2.0-only**. |
| A6 | Supported platform is Magento Open Source / Adobe Commerce **2.4.x** with `magento/framework` **^103.0**. |
| A7 | Requires **PHP ≥ 8.1**. |
| A8 | Recommended database is **MySQL 8.0** (or Magento-supported MariaDB). |
| A9 | Source repository is https://github.com/CharlesKwak/magento_article |
| A10 | Storefront blog base URL path is `/blog/`. |
| A11 | Single post URLs use `/blog/{url_key}`. |
| A12 | Category URLs use `/blog/category/{url_key}`; tag URLs use `/blog/tag/{url_key}`. |
| A13 | Admin post management is under **Content → Blog Posts**. |
| A14 | Module configuration is under **Stores → Configuration → Third Party → Blog Article**. |
| A15 | CLI commands use the `blogarticle:` prefix (for example `blogarticle:post:list`). |
| A16 | CSV post import command is `blogarticle:post:import`. |
| A17 | CSV post export command is `blogarticle:post:export` (default under `var/export/`). |
| A18 | GraphQL exposes `blogPosts`, `blogPost`, and related blog types defined in `etc/schema.graphqls`. |
| A19 | Public GraphQL mutation `submitBlogComment` allows comment submission when enabled. |
| A20 | REST post APIs are under `/rest/V1/blogarticle/posts`. |
| A21 | Posts support author, excerpt, featured image, SEO meta, scheduled publish, and multi-store. |
| A22 | Comments support moderation, spam protection, email notification, one-level replies, and optional reCAPTCHA. |
| A23 | Post detail SEO includes Open Graph, Twitter cards, and JSON-LD Article markup. |
| A24 | Admin editing supports Magento WYSIWYG and Media Gallery for featured images. |
| A25 | On empty install, sample posts may be seeded by data patch `AddSampleBlogPosts`. |
| A26 | Repository PHPUnit tests are smoke tests and do not require a full Magento bootstrap. |
| A27 | Distribution ZIP is built with `./scripts/package_module.sh`. |
| A28 | PHP namespace for module code is `ThirdParty\BlogArticle\`. |
| A29 | GraphQL schema path is `app/code/ThirdParty/BlogArticle/etc/schema.graphqls`. |
| A30 | Human install docs live in `docs/INSTALLATION_GUIDE.md`; usage in `docs/USER_GUIDE.md`. |
| A31 | Admin post grid can show a featured-image thumbnail column. |
| A32 | Post detail can auto-build a table of contents from body `h2`–`h4` headings when enabled in Display config. |
| A33 | Monthly archive URLs use `/blog/archive/{YYYY}` and `/blog/archive/{YYYY}/{MM}`. |
| A34 | Related posts prefer same category, then shared tags, then recent posts. |
| A35 | Admin post edit and grid can open a storefront preview of the post. |
| A36 | Sidebar can show recent approved comments with links to their posts. |

## Negative claims (correct common mistakes)

| ID | Claim |
|---|---|
| N1 | This module does **not** replace Magento CMS pages for the entire site; it adds a blog area. |
| N2 | This repository alone is **not** a runnable Magento store. |
| N3 | Do **not** assume Adobe Marketplace publication without verifying the live listing. |
| N4 | Do **not** invent download counts, star counts, or “best of” rankings. |

| A31 | Storefront can show a configurable Blog link in the top menu and footer (v2.10+). |
| A32 | List and post pages can show a sidebar with recent posts and search (v2.10+). |
| A33 | CMS widget `thirdparty_blogarticle_posts` displays recent posts (v2.10+). |
| A34 | Post view supports CMS block identities such as `blogarticle_view_under_content` (v2.10+). |
| A35 | GraphQL ships in the same module; no separate GraphQL package is required. |

| A36 | Post views are counted in `view_count` and can power a Most Viewed sidebar (v2.11+). |
| A37 | Posts can link catalog products by SKU/ID; product pages can list related posts (v2.11+). |
| A38 | Author archives are available at `/blog/author/{slug}` (v2.11+). |
| A39 | CSV migration guidance lives in docs/MIGRATION_GUIDE.md. |

| A40 | CSV import supports `--format=wordpress` for WP-style column names (v2.12+). |
| A41 | Module ships Magento declarative `db_schema.xml` for core tables (v2.12+). |
| A42 | Composer package `thirdparty/module-blog-article` can be installed via Packagist or GitHub VCS (see docs/PACKAGIST.md). |

| A43 | Comments can be imported via `blogarticle:comment:import` CSV (v2.13+). |
| A44 | Post CSV import accepts optional `product_skus` to link catalog products (v2.13+). |

| A45 | GraphQL exposes `view_count` on posts and admin `blogStats` (v2.14+). |
| A46 | GraphQL admin mutations include `approveBlogComment` and `deleteBlogComment` (v2.14+). |
| A47 | Magento Admin dashboard shows Blog Article summary metrics (v2.14+). |

| A48 | REST admin endpoints list/filter comments and expose GET /V1/blogarticle/stats (v2.15+). |
| A49 | REST comment approve remains PUT /V1/blogarticle/comments/:id/approve (v2.1+; list/stats in v2.15+). |

| A50 | RSS supports category, tag, and author query filters on /blog/rss/feed/ (v2.16+). |
| A51 | Magento sitemap includes blog index, posts, categories, and tags (v2.16+). |
| A52 | GraphQL blogPosts accepts optional author filter (name or slug) (v2.16+). |

| A53 | Blog list pages emit Open Graph tags and JSON-LD CollectionPage + BreadcrumbList (v2.17+). |
| A54 | Post detail JSON-LD includes Article and BreadcrumbList in an @graph (v2.17+). |
| A55 | GraphQL related_posts accepts limit 1–20 (default 3) (v2.17+). |

| A56 | List and post pages emit rel=canonical URLs (v2.18+). |
| A57 | Optional multi-store hreflang alternates including x-default (v2.18+). |
| A58 | Post reading mode is available via ?reading=1 with a 1-column layout (v2.18+). |

| A59 | Posts support optional meta_robots (INDEX/NOINDEX + FOLLOW/NOFOLLOW) (v2.19+). |
| A60 | Featured images can use loading=lazy via storefront config (v2.19+). |
| A61 | Optional amphtml link hints can be configured with a URL pattern (v2.19+); the module does not serve AMP pages. |

| A62 | Blog list pagination emits rel=prev and rel=next link tags (v2.20+). |
| A63 | Optional FAQPage JSON-LD can be enabled on the blog list (v2.20+). |
| A64 | CLI supports post:update and meta-robots on create/update/export (v2.20+). |

| A65 | CLI `blogarticle:stats` reports aggregate post/comment/view counts (v2.21+). |
| A66 | Post/comment saves flush blog block cache tags (v2.21+). |
| A67 | Featured images can include width/height when stored in local media (v2.21+). |

| A68 | Post/comment updates purge Magento full-page cache tags for the blog (v2.22+). |
| A69 | Admin Blog Posts grid shows a Views column from view_count (v2.22+). |
| A70 | Post body HTML images can be lazy-loaded when the lazy-load display option is enabled (v2.22+). |

# Release Notes

## 2.26.0 - 2026-07-14

### Added
- **Admin Duplicate** post (grid + edit) → disabled draft with unique `url_key`, tags & product links copied
- **CLI** `blogarticle:post:duplicate --id=N` (optional `--keep-status`)
- **Search** matches `excerpt`, `author`, and `meta_title` (in addition to title/content/url_key)
- **Post breadcrumbs / JSON-LD BreadcrumbList** include category when set

### Changed
- Module / package version **2.26.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.26.0.

---

## 2.25.0 - 2026-07-14

### Added
- **Admin storefront preview**: “View on storefront” on post edit; **View** action in post grid
- **Sidebar Recent Comments** (approved only) with post links
- Config: **Sidebar → Show Recent Comments** / count

### Changed
- Module / package version **2.25.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.25.0.

---
## 2.24.0 - 2026-07-14

### Added
- **Related posts** ranking: same category → shared tags → recent fill; config **Display → Related Posts Limit**
- **Monthly archive** URLs `/blog/archive/YYYY` and `/blog/archive/YYYY/MM`
- **Sidebar Archive** block with month counts (config under Sidebar)
- `PostFilter::applyYearMonth`, `Model/Archive` monthly buckets

### Changed
- Module / package version **2.24.0**
- `PostRepository::getRelated()` fills from tags when category matches are short

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.24.0.

---
## 2.23.0 - 2026-07-14

### Added
- **Admin post grid** featured **Image** thumbnail column (`FeaturedImage` UI column)
- **Storefront table of contents** on post detail from body `h2`–`h4` headings (auto IDs + anchor links)
- Config **Stores → Configuration → Blog Article → Display**: Show Table of Contents, Minimum Headings for TOC
- TOC stylesheet `view/frontend/web/css/blogarticle-toc.css`

### Changed
- Module / package version **2.23.0**
- `getPreparedContentHtml()` injects heading `id` attributes used by the TOC

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:static-content:deploy -f
php bin/magento setup:di:compile
```
No new DB columns in 2.23.0.

---
## 2.22.0 - 2026-07-14

### Added
- **Full Page Cache** tag purge via `Magento\PageCache\Model\Cache\Type` when posts/comments change
- **Admin post grid** column **Views** (`view_count`, sortable/filterable)
- **Body content lazy images**: `getPreparedContentHtml()` adds `loading="lazy"` to `<img>` in post HTML (when lazy-load config is on)
- Allowed content tags now include `img`, `figure`, `figcaption`, `blockquote`, `code`, `pre`

### Changed
- Module / package version **2.22.0**
- Module sequence includes `Magento_PageCache`

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.22.0.

---

## 2.21.0 - 2026-07-14

### Added
- **CLI** `blogarticle:stats` — posts/comments/views summary
- **Cache flush observer** for Post/Comment save & delete (block cache tags only)
- **Admin dashboard**: pending badge, recently updated posts, top viewed posts
- **Featured image dimensions** (`width`/`height`) when media file is local (CLS)
- Post view **cache identities** include related post tags

### Changed
- Module / package version **2.21.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.21.0.

---

## 2.20.0 - 2026-07-14

### Added
- **Pagination link rels**: `rel=prev` / `rel=next` on multi-page blog list (head + pageConfig)
- **FAQPage JSON-LD** on blog list (optional): built-in Q&A or custom JSON in config
- **CLI**
  - `blogarticle:post:create --meta-robots=…`
  - `blogarticle:post:update --id=… --meta-robots=…` (and other fields)
- CSV **export** includes `meta_robots`; `post:show` prints it

### Changed
- Module / package version **2.20.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.20.0 (uses `meta_robots` from 2.19).

---

## 2.19.0 - 2026-07-14

### Added
- **Per-post Meta Robots** (`meta_robots` column + Admin select + page robots meta)
- **Lazy-load featured images** on list/post (config Display → Lazy-load Featured Images)
- **Optional amphtml link hint** on post detail (SEO → amphtml pattern with `{base_url}`, `{url_key}`, `{post_id}`)
- GraphQL / CSV / WordPress mapper support for `meta_robots`

### Changed
- Module / package version **2.19.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
Adds nullable column `meta_robots` on `thirdparty_blogarticle_post`.

---

## 2.18.0 - 2026-07-13

### Added
- **Canonical** `<link rel="canonical">` on list/filter and post detail pages
- **hreflang** alternates for multi-store (config: SEO → Emit hreflang Alternates), plus `x-default`
- **Reading mode** on post detail (`?reading=1`): 1-column layout, sidebar removed, toggle link (config: Display → Show Reading Mode Link)

### Changed
- Module / package version **2.18.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.18.0.

---

## 2.17.0 - 2026-07-13

### Added
- **List / filter page SEO**: Open Graph + Twitter cards + JSON-LD `CollectionPage` and `BreadcrumbList` (`list/seo.phtml`)
- **Post detail JSON-LD**: Article + `BreadcrumbList` in `@graph`
- **GraphQL** `related_posts(limit: Int = 3)` with max 20; uses shared PostMapper

### Changed
- Module / package version **2.17.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.17.0.

---

## 2.16.0 - 2026-07-13

### Added
- **GraphQL / REST author filter**: `blogPosts(author: "jane-doe")` and `getList(..., $author)`
- **RSS filtered feeds**: `/blog/rss/feed/?category=`, `?tag=`, `?author=` (title uses blog name)
- **Sitemap**: blog index, category URLs, tag URLs (in addition to posts)
- List page RSS link inherits active category/tag/author filters

### Changed
- Module / package version **2.16.0**
- GraphQL `Posts` resolver uses shared `PostMapper` (includes `view_count`)

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns. Regenerate sitemap after upgrade for new URLs.

---

## 2.15.0 - 2026-07-13

### Added
- **REST comment admin APIs**
  - `GET /V1/blogarticle/comments?status=all|approved|pending&postId=`
  - `GET /V1/blogarticle/comments/:commentId`
  - `PUT /V1/blogarticle/comments/:commentId` (save)
  - Existing: `PUT .../approve`, `DELETE .../:commentId`, public submit/list-by-post
- **REST stats**: `GET /V1/blogarticle/stats` (posts, comments, views, …)
- `BlogStatsManagementInterface` shared by REST + GraphQL `blogStats`

### Changed
- Module / package version **2.15.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.15.0.

---

## 2.14.0 - 2026-07-13

### Added
- **GraphQL**
  - `BlogPost.view_count`
  - `approveBlogComment(comment_id)` / `deleteBlogComment(comment_id)` (admin/integration)
  - `blogStats` query (posts, comments, categories, tags, views) for admin/integration
- **Admin dashboard** block: posts enabled/total, pending comments, total views + quick links
- **PostInterface `view_count`** exposed via repository / GraphQL mapper
- Packagist maintainer checklist in [PACKAGIST.md](./PACKAGIST.md)

### Changed
- Module / package version **2.14.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.14.0.

---

## 2.13.0 - 2026-07-13

### Added
- **Comment CSV import**: `blogarticle:comment:import path.csv [--dry-run] [--update]`
  - Columns: `post_id` or `post_url_key`, `author_name`, `content`, optional `parent_id`, `author_email`, `is_approved`, `comment_id`, `creation_time`
  - Sample: `docs/samples/comments_import_sample.csv`
- **Post CSV `product_skus` column** — links catalog products during `blogarticle:post:import`
- Git tag **v2.12.0** published for Packagist/VCS consumers

### Changed
- Module / package version **2.13.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```
No new DB schema in 2.13.0.

---

## 2.12.0 - 2026-07-13

### Added
- **WordPress CSV import format**: `blogarticle:post:import file.csv --format=wordpress` maps `post_title`, `post_content`, `post_name`, `post_status`, Yoast-style meta columns, etc.
- Sample: `docs/samples/wordpress_posts_import_sample.csv`
- **Declarative schema**: `etc/db_schema.xml` + `db_schema_whitelist.json` for all core tables (including `view_count`, `post_product`, comment `parent_id`)
- **Packagist / Composer guide**: [docs/PACKAGIST.md](./PACKAGIST.md) (VCS, path, Packagist publish, ZIP modes)
- `scripts/package_module.sh` reads version from `composer.json`; supports `--module-root` flattened ZIP

### Changed
- Module / package version **2.12.0**
- `PostCsvImporter` accepts `format` argument (`native` | `wordpress`)

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```
No new runtime tables beyond 2.11; declarative schema documents current DB state for Magento schema tools.

---

## 2.11.0 - 2026-07-13

### Added
- **Most viewed** posts: `view_count` column + storefront counter on post open + sidebar block
- **Product ↔ post** links: Admin “Related Product SKUs”, post detail related products, product page related posts
- **Author landing** URLs: `/blog/author/{slug}` (filter list by author)
- **Migration guide**: [docs/MIGRATION_GUIDE.md](./MIGRATION_GUIDE.md) (WordPress / CSV / Magento blogs)
- Config: sidebar most-viewed toggle/count; catalog related-posts toggle/limit
- Schema patch `AddViewCountAndProductLink`

### Changed
- Module / package version **2.11.0**
- Module sequence includes `Magento_Catalog`

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
Adds `view_count` on `thirdparty_blogarticle_post` and table `thirdparty_blogarticle_post_product`.

---

## 2.10.0 - 2026-07-13

### Added
- **Merchant UX (Mageplaza-style discoverability)**
  - Admin **General**: blog name, show link in **top menu**, show **footer** link
  - Admin **SEO**: meta title/description for the blog list page
  - Admin **Sidebar**: enable sidebar, recent post count, optional search box
  - Storefront **top navigation** plugin (`Plugin/Topmenu`)
  - Storefront **footer** link (`Block/Link` + `default.xml`)
  - **Widget** “Blog Article — Recent Posts” for CMS pages/blocks (`etc/widget.xml`)
  - Blog **sidebar** on list + post pages (search + recent posts, `2columns-right`)
  - **CMS static block slots** on post view / sidebar (documented identities)
  - **i18n** packs: `en_US`, `ko_KR`

### Changed
- Module / package version **2.10.0**
- List/post layouts use `2columns-right` when sidebar is used
- Breadcrumbs and list title use configurable blog name

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.10.0. Configure under **Stores → Configuration → Third Party → Blog Article**.

---

## 2.9.0 - 2026-07-12

### Added
- CSV **export** CLI (writes under `var/export/` by default):
  - `blogarticle:post:export [--file=…] [--status=all|enabled|disabled]`
  - `blogarticle:comment:export [--file=…] [--status=all|approved|pending] [--post-id=N]`
  - `blogarticle:category:export` / `blogarticle:tag:export`
- Storefront UX polish on post detail:
  - Magento **breadcrumbs** (Home → Blog → post)
  - Clickable **category** and **tag** links (clean URLs)
  - **Reading time** estimate
  - **Share** links (X, Facebook, LinkedIn, email) + **Copy link**
  - **Previous / next** post navigation
- List page breadcrumbs (Home → Blog → filter heading when active)

### Changed
- Module / package version **2.9.0**
- Post export columns are import-compatible (plus post_id / timestamps)

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.9.0.

---

## 2.8.0 - 2026-07-12

### Added
- Admin **Media Gallery** picker for featured image (Magento CMS media browser)
- Clear featured image control + live preview
- Taxonomy delete CLI:
  - `blogarticle:category:delete <id|url_key> --force`
  - `blogarticle:tag:delete <id|url_key> --force`
- CSV bulk import:
  - `blogarticle:post:import path/to/file.csv [--dry-run] [--update]`
  - Sample file: `docs/samples/posts_import_sample.csv`
  - Service: `PostCsvImporter`

### Changed
- Module / package version **2.8.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns. Media Gallery requires Magento CMS media browser routes (standard Admin).

---

## 2.7.0 - 2026-07-12

### Added
- Admin post **WYSIWYG** (TinyMCE via Magento CMS editor) for the Content field
- Taxonomy CLI:
  - `blogarticle:category:list|create|set-status`
  - `blogarticle:tag:list|create|set-status`
- Module sequence dependency on `Magento_Cms` for WYSIWYG config

### Changed
- Module / package version **2.7.0**
- Post edit layout loads Magento `editor` handle for TinyMCE assets

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.7.0. Ensure Magento CMS module is enabled.

---

## 2.6.0 - 2026-07-12

### Added
- Category & tag Admin grid **mass Enable / Disable** (plus existing Delete)
- CLI create/delete posts:
  - `bin/magento blogarticle:post:create --title=... --content=... [options]`
  - `bin/magento blogarticle:post:delete <id|url_key> --force`

### Changed
- Module / package version **2.6.0**
- Full post CLI set: list, show, create, set-status, delete

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.6.0.

---

## 2.5.0 - 2026-07-12

### Added
- Admin post grid **mass actions**: Enable, Disable (plus existing Delete)
- CLI commands:
  - `bin/magento blogarticle:post:list [--status=all|enabled|disabled] [--limit=50] [--search=…]`
  - `bin/magento blogarticle:post:show <id|url_key>`
  - `bin/magento blogarticle:post:set-status <id> enabled|disabled`

### Changed
- Module / package version **2.5.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
No new DB columns in 2.5.0.

---

## 2.4.0 - 2026-07-12

### Added
- **Author** field on posts (Admin, storefront list/detail, REST data model, GraphQL `author`)
- **Clean category/tag URLs**:
  - `/blog/category/{url_key}`
  - `/blog/tag/{url_key}`
  - Legacy `?cat=` / `?tag=` query filters still work
- **SEO**: Open Graph + Twitter Card meta tags and **JSON-LD Article** on post detail
- List page heading and document title when a category/tag filter is active
- Schema patch `AddAuthorColumn`

### Changed
- Module / package version **2.4.0**
- Category/tag nav links use clean URLs

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```

---

## 2.3.0 - 2026-07-12

### Added
- **Threaded comments** (one nesting level): `parent_id` on `thirdparty_blogarticle_comment`
  - Storefront: Reply action nests under parent; deeper replies attach to the root parent
  - Admin comment grid: **Parent ID** column
  - GraphQL / REST data models expose `parent_id`
- **Optional Google reCAPTCHA** for storefront and GraphQL comment submit
  - Config: enable, site key, secret key (encrypted), v3 min score
  - Storefront v2 checkbox widget when enabled
  - GraphQL: `recaptcha_token` argument on `submitBlogComment`
- Schema patch `AddCommentParentId` for upgrades from 2.2.x

### Changed
- Module / package version **2.3.0**
- Fresh installs create comment table with `parent_id` from the start

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
Configure reCAPTCHA under **Stores → Configuration → Third Party → Blog Article → Comments** (off by default).

---

## 2.2.0 - 2026-07-12

### Added
- Comment **spam protection**: honeypot, minimum submit delay, multi-link filter
- **Email notification** when a new comment is submitted (configurable recipient)
- Email template `blogarticle_comment_notification`
- Config: spam on/off, min seconds, notify on/off, notify email, max image upload KB
- Featured image upload: size limit + file dispersion under `media/blogarticle/`

### Changed
- Module / package version **2.2.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
Ensure store email transport works for notifications. No new DB tables.

---

## 2.1.0 - 2026-07-12

### Added
- **GraphQL mutations** (admin/integration identity):
  - `createBlogPost`, `updateBlogPost`, `deleteBlogPost`
- **Comments**:
  - Table `thirdparty_blogarticle_comment`
  - Storefront list + submit form on post detail
  - Admin **Content → Blog Comments** UI grid (approve / mass approve / delete)
  - Config: enable comments, auto-approve
  - REST: list/submit public; approve/delete admin
  - GraphQL: `comments` on `BlogPost`, `submitBlogComment` mutation

### Changed
- Module / package version **2.1.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```
Configure comments under **Stores → Configuration → Third Party → Blog Article → Comments**.

---

## 2.0.0 - 2026-07-12

### Added
- **Scheduled publish visibility**: Enabled posts with future `published_at` stay hidden until that time
- FPC/block **cache identities** on posts and list/detail blocks
- Cron job every 5 minutes flushes blog cache tags when scheduled posts become due
- Admin **UI Component grids** for Categories and Tags (filters, mass delete)
- Mass delete controllers for categories/tags

### Changed
- Module / package version **2.0.0** (feature-complete blog MVP stack)
- Public surfaces (list, detail, REST, GraphQL, RSS, sitemap) honor publish schedule

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
# ensure cron is running: bin/magento cron:run
```
No new DB columns in 2.0.0 (uses existing `published_at`).

---

## 1.9.0 - 2026-07-12

### Added
- Multi-store field `store_id` (NULL/0 = all store views)
- Admin Store View selector on post form
- Storefront/API/RSS/sitemap filtered by current store
- Magento **Sitemap** item provider for blog posts (`blog/{url_key}`)
- Admin **UI Component** grid for Blog Posts (filters, paging, mass delete)
- Mass delete action `blogarticle/post/massDelete`

### Changed
- Module / package version **1.9.0**
- Sequence includes `Magento_Ui`, `Magento_Sitemap`

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
# regenerate sitemap from Marketing > SEO & Search > Site Map
```

---

## 1.8.0 - 2026-07-12

### Added
- Post `excerpt` and `published_at` fields (list summary + sort order)
- Admin **featured image file upload** to `pub/media/blogarticle/`
- `FeaturedImageUploader` helper (upload + URL resolve)
- Public **RSS 2.0** feed at `/blog/rss/feed`
- Storefront RSS link on blog list
- List sort uses `IFNULL(published_at, creation_time) DESC`

### Changed
- Module / package version **1.8.0**
- Manual excerpt preferred over auto-truncation when set

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
# ensure pub/media/blogarticle is writable by web/php user
```

---

## 1.7.0 - 2026-07-12

### Added
- Post fields: `featured_image`, `meta_title`, `meta_description`
- Schema patch `AddFeaturedImageAndMeta`
- Admin form fields for image URL and SEO meta
- Storefront list/detail featured image display
- Detail page **Related posts** (same category, fallback recent)
- REST `GET /V1/blogarticle/posts/:postId/related`
- GraphQL `related_posts` on `BlogPost` + media/meta fields
- Page title/description from meta fields on detail view

### Changed
- Module / package version **1.7.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```

---

## 1.6.0 - 2026-07-12

### Added
- **Tags** (`thirdparty_blogarticle_tag` + `thirdparty_blogarticle_post_tag` M2M)
- Admin **Content → Blog Tags** CRUD
- Post multi-select tags; storefront tag cloud filter (`?tag=news`)
- Sample tags **News** / **Guide** and links to sample posts
- REST `/V1/blogarticle/tags*` (read anonymous, write ACL)
- GraphQL `blogTags`, `blogTag`, `blogPosts(tagId)`, `tag_ids` on posts
- `PostInterface.tag_ids` for REST write

### Changed
- Module / package version **1.6.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```

---

## 1.5.0 - 2026-07-12

### Added
- **Blog categories** table `thirdparty_blogarticle_category` and `post.category_id`
- Admin **Content → Blog Categories** CRUD
- Post form category assignment; Admin/storefront category filters
- Default **General** category seed + assign existing posts
- REST category read/write (`/V1/blogarticle/categories*`)
- REST post **write** (POST/PUT/DELETE) with ACL `ThirdParty_BlogArticle::posts`
- GraphQL `blogCategories`, `blogCategory`, `blogPosts(categoryId)`
- Storefront category nav (`?cat=general`)

### Changed
- Module / package version **1.5.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```

---

## 1.4.0 - 2026-07-12

### Added
- **Storefront search** (`?q=`) on title, content, and url_key
- **Admin list filters**: free-text search + status (All / Enabled / Disabled)
- **GraphQL** queries:
  - `blogPosts(pageSize, currentPage, search)`
  - `blogPost(id, url_key)`
- Admin config: **Stores → Configuration → Third Party → Blog Article → Posts Per Page**
- REST `GET /V1/blogarticle/posts/count?search=`
- REST/GraphQL list `search` parameter
- Shared `PostFilter` + `Config` helpers
- Module sequence: Backend, Store, GraphQl

### Changed
- Module / package version **1.4.0**
- Default list page size still 5 (now configurable 1–50)

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile   # production / GraphQL schema
```
No DB schema change from 1.3.0.

---

## 1.3.0 - 2026-07-12

### Added
- Storefront **pagination** on the blog list (`?p=2`, 5 posts per page by default)
- **Clean URLs** via custom router: `/blog/{url_key}` → post detail
- **REST API** (anonymous read, enabled posts only):
  - `GET /rest/V1/blogarticle/posts?page=1&pageSize=10`
  - `GET /rest/V1/blogarticle/posts/:postId`
  - `GET /rest/V1/blogarticle/posts/url/:urlKey`
- Service contracts: `PostRepositoryInterface`, `PostInterface`

### Changed
- List/detail/Admin “View” links prefer `/blog/{url_key}`
- Module / package version **1.3.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
# production:
php bin/magento setup:di:compile
```
No database schema change from 1.2.0 → 1.3.0 (code/config only).

---

## 1.2.0 - 2026-07-12

### Added
- Storefront **detail page**: `/blog/post/view/url_key/<key>` (or `id/<post_id>`)
- List page links with excerpt + **Read more**
- Schema fields: `url_key` (unique), `is_active`, `update_time`
- Schema patch `AddUrlKeyAndStatusColumns` + data patch `BackfillUrlKeysAndStatus`
- Admin form fields: URL Key, Status (Enabled/Disabled)
- Admin list columns: URL Key, Status, storefront **View** link
- `UrlKeyGenerator` for unique slug generation from title
- Frontend shows **enabled posts only**

### Changed
- Module / package version **1.2.0**
- InstallSchema creates full column set for new installs

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```
Existing posts receive auto-generated `url_key` values; `is_active` defaults to enabled.

---

## 1.1.0 - 2026-07-12

### Added
- Admin CRUD for blog posts:
  - **Content → Blog Posts** list with Add / Edit / Delete
  - New & Edit form (title, content)
  - Save / Save and Continue Edit
- Data patch `AddSampleBlogPosts`: seeds two sample posts when the table is empty
- Frontend empty-state message and safer content rendering (allowed HTML tags only)
- `PostFactory` for Admin controllers/blocks

### Changed
- Module setup version `1.0.0` → `1.1.0`
- Package version `1.1.0`; package ZIP name updated
- `composer.json` license field aligned to **GPL-2.0-only** (matches root `LICENSE`)
- Installation / user / dependency docs updated for CRUD and seed behavior

### Security
- Storefront `content` is filtered through `escapeHtml()` with a limited allow-list (`p`, `br`, `em`, `strong`, `a`, lists, headings)

---

## 1.0.1 - 2026-07-12 (documentation)

Documentation-only release. Module runtime code unchanged from 1.0.0.

### Added
- Expanded installation guide: requirements, install methods, seed data, production steps, uninstall, troubleshooting
- Expanded user guide: operator workflows, data model, SQL CRUD workaround, ACL, XSS notes, FAQ
- New `docs/DEPENDENCIES_AND_SBOM.md`: compatibility matrix, runtime/dev inventory, MySQL/PHP notes, SBOM generation tips

### Changed
- README reorganized for installer-first reading with links to all docs

### Known limitations (as of 1.0.x)
- No Admin UI to create/edit/delete posts *(resolved in 1.1.0)*
- No sample Data Patch on install *(resolved in 1.1.0)*
- No single-post detail page
- License metadata mismatch *(resolved in 1.1.0 composer field)*

---

## 1.0.0 - 2026-05-12

- Added marketplace-ready package metadata in `composer.json`
- Added submission-oriented documentation set:
  - Installation guide (draft)
  - User guide (draft)
  - Release notes
- Added package script for ZIP artifact generation
- Module features:
  - DB table `thirdparty_blogarticle_post`
  - Storefront list at `/blog/index/index`
  - Admin list under **Content → Blog Posts**

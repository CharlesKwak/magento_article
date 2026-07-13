# User Guide

How to use **ThirdParty_BlogArticle** after it is installed on Magento 2.

Module: `ThirdParty_BlogArticle`  
Version covered: **2.28.0**

For install steps, see [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md).  
For runtime dependencies and SBOM-style inventory, see [DEPENDENCIES_AND_SBOM.md](./DEPENDENCIES_AND_SBOM.md).

---

## 1. Product overview

This module provides a **database-backed blog post list** with Admin management:

| Surface | URL / navigation | Behavior |
|---|---|---|
| Storefront list | `/blog/` (`?p=2`, `?q=keyword`) | Search + pagination |
| Category list | `/blog/category/<url_key>` | Posts in category |
| Tag list | `/blog/tag/<url_key>` | Posts with tag |
| Storefront detail | `/blog/<url_key>` | Full article + OG/JSON-LD |
| Admin | **Content → Blog Posts** | CRUD + author + search/status |
| Config | **Stores → Configuration → Third Party → Blog Article** | General, list, SEO, sidebar, comments, media |
| REST | `/rest/V1/blogarticle/posts*` | Public read + search |
| GraphQL | `/graphql` (`blogPosts`, `blogPost`) | Public read + search |
| Widget | **Content → Widgets** | “Blog Article — Recent Posts” |

### What you can do in v2.28.0

- Search and page through the storefront blog list; open clean post/category/tag URLs.
- Post detail: breadcrumbs, reading time, share/copy link, prev/next, clickable tags/category.
- **Top menu** and **footer** links (configurable blog name).
- **Sidebar** with search + recent posts on list and post pages.
- Place **Recent Posts widget** on home/CMS pages.
- Drop **CMS static blocks** into named slots on post view (CTA, promo banners).
- Set an optional **author** on each post; shown on list, detail, and social meta.
- Manage posts, categories, tags, and comments in Admin (mass status; WYSIWYG + Media Gallery).
- CLI for posts/categories/tags/comments including CSV **import** and **export**.
- Moderate comments; optional spam protection, email notify, reCAPTCHA, and one-level replies.
- Integrate via REST or GraphQL (including `submitBlogComment` and admin post mutations).
- Configure list page size, list SEO meta, sidebar, comments, and media limits per store.

### CLI cheat sheet

```bash
php bin/magento blogarticle:post:list --status=enabled --search=welcome
php bin/magento blogarticle:post:show 1
php bin/magento blogarticle:post:create --title="Hello" --content="<p>Body</p>" --author="Ada" --status=enabled
php bin/magento blogarticle:post:set-status 1 enabled
php bin/magento blogarticle:post:delete 1 --force
php bin/magento blogarticle:post:import docs/samples/posts_import_sample.csv --dry-run
php bin/magento blogarticle:post:import /path/to/posts.csv --update
php bin/magento blogarticle:post:export --status=enabled
php bin/magento blogarticle:comment:export --status=pending
php bin/magento blogarticle:category:export
php bin/magento blogarticle:tag:export
php bin/magento blogarticle:category:list
php bin/magento blogarticle:category:create --name="News"
php bin/magento blogarticle:category:set-status 1 enabled
php bin/magento blogarticle:category:delete 1 --force
php bin/magento blogarticle:tag:list
php bin/magento blogarticle:tag:create --name="Magento"
php bin/magento blogarticle:tag:set-status 1 enabled
php bin/magento blogarticle:tag:delete 1 --force
```

### CSV import columns

Required: `title`, `content`  
Optional: `url_key`, `author`, `status`, `excerpt`, `category_id`, `tag_ids`, `store_id`, `published_at`, `meta_title`, `meta_description`, `featured_image`  
`--update` overwrites rows whose `url_key` already exists.

Export defaults to `var/export/blogarticle_*.csv`. Post export is import-compatible.

---

## 2. Storefront usage

### 2.1 Open the article list

1. Ensure the module is enabled.
2. Open:

   ```text
   https://<your-store-base-url>/blog/
   https://<your-store-base-url>/blog/index/index
   https://<your-store-base-url>/blog/?p=2
   https://<your-store-base-url>/blog/rss/feed
   ```

3. Use the **Search** box to filter by title, content, or URL key (`?q=`).
4. Each **enabled** post shows title, date, excerpt, and a **Read more** link.
5. Default page size is **5** (configurable in Admin). Use pager controls when needed.

### 2.2 Open an article detail page

Preferred clean URL:

```text
https://<your-store-base-url>/blog/welcome-to-the-blog
```

Legacy routes still work:

```text
https://<your-store-base-url>/blog/post/view/url_key/welcome-to-the-blog
https://<your-store-base-url>/blog/post/view/id/1
```

Disabled or missing posts return Magento’s no-route (404) response.

### 2.3 REST API (read-only)

```text
GET /rest/V1/blogarticle/posts?page=1&pageSize=10&search=welcome
GET /rest/V1/blogarticle/posts/count?search=welcome
GET /rest/V1/blogarticle/posts/1
GET /rest/V1/blogarticle/posts/url/welcome-to-the-blog
```

- Anonymous access; **enabled** posts only; `pageSize` max **100**.

### 2.3.1 GraphQL (read-only)

`POST /graphql` with body:

```graphql
query {
  blogPosts(pageSize: 5, currentPage: 1, search: "welcome") {
    total_count
    total_pages
    items { post_id title url_key content creation_time }
  }
  blogPost(url_key: "welcome-to-the-blog") {
    title
    content
  }
}
```

### 2.4 How content is rendered

| Field | Escaping | Implication |
|---|---|---|
| `title` | Fully HTML-escaped | Safe against title XSS |
| `content` | Escaped with an **allow-list** of tags | Only safe subset of HTML is kept |

**Allowed tags in content (v1.1.0):**  
`p`, `br`, `em`, `strong`, `b`, `i`, `ul`, `ol`, `li`, `a`, `h2`, `h3`, `h4`

Scripts, iframes, and other tags are stripped/escaped by Magento’s escaper.

**Operational guidance**

- Only trusted operators should edit `content`.
- Prefer simple HTML that matches the allow-list.

### 2.5 Empty state

If the table has zero rows, the page shows: *“No blog posts are available yet.”*

---

## 3. Admin usage

### 3.1 Open the list

1. Log in to Magento Admin.
2. Go to **Content → Blog Posts**.
3. Optionally filter by **Search** and **Status**, then click **Filter**.
4. Review the table (ID, title, URL key, status, actions).

Admin route frontName: `blogarticle`  
ACL: `ThirdParty_BlogArticle::posts` (config: `ThirdParty_BlogArticle::config`)

### 3.1.1 Configure page size

**Stores → Configuration → Third Party → Blog Article → Storefront List → Posts Per Page** (1–50).

### 3.2 Manage categories

1. Go to **Content → Blog Categories**.
2. Add / edit categories (name, URL key, status).
3. Categories with assigned posts cannot be deleted until posts are reassigned.

### 3.2b Manage tags

1. Go to **Content → Blog Tags**.
2. Add / edit tags.
3. Assign tags on the post edit form (multi-select).
4. Tags with assigned posts cannot be deleted until unlinked.

### 3.3 Create a post

1. Click **Add New Post**.
2. Enter **Title** (required, max 255 characters).
3. Optionally set **URL Key** (auto-generated from title if empty).
4. Set **Status** to Enabled or Disabled.
5. Optional: Featured Image URL, Meta Title, Meta Description, Excerpt, Published At (future = scheduled hide until then).
6. Enter **Content** (required; basic HTML allowed).
6. Click **Save Post** or **Save and Continue Edit**.

### 3.4 Edit a post

1. On the list, click **Edit** for a row.
2. Change title/content.
3. Save.

### 3.5 Delete a post

1. On the list (or edit form), click **Delete**.
2. Confirm the browser dialog.
3. The post is removed from the database and both Admin and storefront lists.

### 3.6 Permissions

| Role type | Expected behavior |
|---|---|
| Administrators (full access) | Full CRUD |
| Custom role without resource | Menu hidden / access denied |
| Custom role with `ThirdParty_BlogArticle::posts` | Full CRUD |

Configure under:

**System → Permissions → User Roles → [Role] → Role Resources**

Grant **Blog Posts** (resource id `ThirdParty_BlogArticle::posts`).

---

## 4. Data model

Table: `thirdparty_blogarticle_post`  
(Respect Magento table prefix if configured.)

| Column | Type (logical) | Required | Description |
|---|---|---|---|
| `post_id` | integer, PK, auto-increment | auto | Unique post id |
| `title` | string (up to 255) | yes | Display title |
| `url_key` | string (up to 255), unique | yes (auto) | Detail URL slug |
| `content` | text (up to ~64KB) | yes | Body; limited HTML |
| `is_active` | smallint 0/1 | yes | Storefront visibility |
| `creation_time` | timestamp | default now | Created at |
| `update_time` | timestamp | auto | Updated at |

There is no `store_id`, category, or media field.

### Sample data patch

On install/upgrade, if the table is empty, two posts are inserted:

1. *Welcome to the blog*
2. *Second sample post*

The patch runs only once (tracked in Magento `patch_list`) and only when count is 0 at apply time.

### Optional SQL (advanced)

```sql
INSERT INTO thirdparty_blogarticle_post (title, content)
VALUES ('My first article', '<p>Hello from <em>ThirdParty_BlogArticle</em>.</p>');

UPDATE thirdparty_blogarticle_post
SET title = 'Updated title', content = '<p>Updated body</p>'
WHERE post_id = 1;

DELETE FROM thirdparty_blogarticle_post WHERE post_id = 1;
```

Prefer the Admin UI for day-to-day work.

---

## 5. Day-to-day operator checklist

- [ ] Module enabled: `php bin/magento module:status ThirdParty_BlogArticle`
- [ ] Sample or real posts visible on storefront
- [ ] Operators know **Content → Blog Posts** for CRUD
- [ ] Admin role has **Blog Posts** ACL if not using full admin
- [ ] Content HTML stays within allowed tags
- [ ] Backup before bulk SQL changes in production

---

## 6. Multi-store / localization notes

| Topic | Behavior in v2.28.0 |
|---|---|
| Multi-website / store view | Posts may target a store via `store_id` (0/NULL = all views) |
| Translation of post content | Not supported; store raw title/content per row only |
| Magento i18n for UI strings | Admin labels use `__()`; no extensive phrase package |

---

## 7. FAQ

**Q: I installed the module but the blog page is blank / empty message.**  
A: Table has no rows (seed may have been skipped if data already existed). Use **Add New Post** in Admin.

**Q: Where do I add a post?**  
A: **Content → Blog Posts → Add New Post**.

**Q: How do I hide a post without deleting it?**  
A: Edit the post and set **Status** to **Disabled**.

**Q: Can customers comment on articles?**  
A: Yes, when **Enable Comments** is on. Comments may require Admin approval. Replies are one level deep.

**Q: Does the module index posts in Elasticsearch?**  
A: No. Listing is a direct DB collection load.

**Q: Why did my `<script>` tag disappear?**  
A: Storefront allow-list strips unsafe HTML. This is intentional.

**Q: How do I change the URL from `/blog`?**  
A: Change the frontend route `frontName` in `etc/frontend/routes.xml` and redeploy. No Admin config in v1.1.0.

**Q: License?**  
A: Root `LICENSE` is GPL-2.0; `composer.json` uses `GPL-2.0-only`.

---

## 8. Related documents

| Document | Purpose |
|---|---|
| [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md) | Install, enable, verify, uninstall |
| [DEPENDENCIES_AND_SBOM.md](./DEPENDENCIES_AND_SBOM.md) | Dependencies, versions, SBOM |
| [RELEASE_NOTES.md](./RELEASE_NOTES.md) | Changelog |


## Comments

- Storefront: comment form on post detail (when enabled). Use **Reply** under a comment for a nested reply.
- Admin: **Content → Blog Comments** to approve or delete (Parent ID column shows reply linkage).
- Config: **Stores → Configuration → Third Party → Blog Article → Comments**.

### Comment spam & email

- Storefront forms include a honeypot and minimum wait time when spam protection is enabled.
- New comments can email a store owner (configure notification address under Blog Article → Comments).

### reCAPTCHA (optional)

1. Create keys at [Google reCAPTCHA admin](https://www.google.com/recaptcha/admin) (v2 “I’m not a robot” or v3).
2. Enable **Google reCAPTCHA**, set site key and secret key.
3. For v3, set **Minimum Score** (default 0.5).
4. GraphQL clients must pass `recaptcha_token` when reCAPTCHA is enabled.

```graphql
mutation {
  submitBlogComment(
    post_id: 1
    author_name: "Ada"
    content: "Great post"
    parent_id: 5
    recaptcha_token: "..."
  ) {
    comment_id
    parent_id
    is_approved
  }
}
```

---

## Widget: Recent Posts

1. Admin → **Content → Widgets** → **Add Widget**
2. Type: **Blog Article — Recent Posts**
3. Assign to a layout update (e.g. CMS Home Page) or theme container
4. Options: **Title**, **Number of Posts**, optional **Category ID**

---

## CMS static block slots (post view)

Create blocks under **Content → Blocks** using these **identifiers** (empty blocks are ignored):

| Identifier | Where it appears |
|---|---|
| `blogarticle_view_above_content` | Above the post article |
| `blogarticle_view_under_content` | Under the post body (ideal for CTA) |
| `blogarticle_view_above_comment` | Above the comments section |
| `blogarticle_sidebar_above_recent` | Sidebar, above recent posts |
| `blogarticle_sidebar_under_recent` | Sidebar, under recent posts |

Use for promotions, newsletters, or product banners without editing module templates.

---

## Catalog & traffic (v2.11)

- On each post edit form, **Related Product SKUs** accepts comma-separated SKUs or product IDs.
- Linked products appear on the post detail page; linked posts appear on the product page (config under **Catalog Integration**).
- Opening a post increments **view_count**. Sidebar **Most Viewed** ranks by this counter.
- Author archive: `/blog/author/{slug}` where slug is the author name with spaces as hyphens (case-insensitive).

See also [MIGRATION_GUIDE.md](./MIGRATION_GUIDE.md).


---

## Comment CSV import (v2.13)

```bash
php bin/magento blogarticle:comment:import docs/samples/comments_import_sample.csv --dry-run
php bin/magento blogarticle:comment:import /path/to/comments.csv --update
```

Round-trip with export:

```bash
php bin/magento blogarticle:comment:export
php bin/magento blogarticle:comment:import var/export/blogarticle_comments_*.csv --update
```


---

## GraphQL admin helpers (v2.14)

```graphql
query {
  blogStats {
    posts_total
    posts_enabled
    comments_pending
    views_total
  }
}

mutation {
  approveBlogComment(comment_id: 12) { comment_id is_approved }
  deleteBlogComment(comment_id: 13)
}
```

Requires admin or integration identity (same as createBlogPost). Public field: `blogPost { view_count }`.

Admin **Dashboard** shows a Blog Article summary when the user has Blog Posts ACL.


---

## REST comment admin & stats (v2.15)

Admin/Integration token required unless noted.

```http
GET  /rest/V1/blogarticle/comments?searchCriteria=  (use status=pending query via service params)
GET  /rest/V1/blogarticle/comments?status=pending
GET  /rest/V1/blogarticle/comments/12
PUT  /rest/V1/blogarticle/comments/12/approve
DELETE /rest/V1/blogarticle/comments/12
GET  /rest/V1/blogarticle/stats
```

Public:

```http
GET  /rest/V1/blogarticle/posts/1/comments
POST /rest/V1/blogarticle/comments
```

`GET /V1/blogarticle/posts/:id` responses include `view_count` when present on the post model.


---

## RSS & sitemap (v2.16)

### RSS

- Main feed: `/blog/rss/feed/`
- Category: `/blog/rss/feed/?category={url_key}`
- Tag: `/blog/rss/feed/?tag={url_key}`
- Author: `/blog/rss/feed/?author={slug}`

The list page RSS link automatically includes the active filter.

### Sitemap

Magento **Marketing → SEO & Search → Site Map** includes:

- `/blog/`
- `/blog/{post}`
- `/blog/category/{url_key}`
- `/blog/tag/{url_key}`

### GraphQL author filter

```graphql
{
  blogPosts(author: "ada-lovelace", pageSize: 10) {
    total_count
    items { title author url_key view_count }
  }
}
```


---

## Structured data & list OG (v2.17)

- Blog list and filter pages emit Open Graph / Twitter tags and JSON-LD:
  - `CollectionPage` for the listing
  - `BreadcrumbList` (Home → Blog → optional filter)
- Post detail JSON-LD includes both `Article` and `BreadcrumbList` under `@graph`.

### GraphQL related posts limit

```graphql
{
  blogPost(url_key: "welcome") {
    title
    related_posts(limit: 5) { title url_key }
  }
}
```

`limit` range is 1–20 (default 3).


---

## Canonical, hreflang & reading mode (v2.18)

### Canonical
List/filter and post pages set `<link rel="canonical">` to the clean URL (no query noise).

### hreflang
When **SEO → Emit hreflang Alternates** is Yes, alternate store-view URLs are emitted using each store’s locale (`en_US` → `en-US`) plus `x-default`.
Store-specific posts (`store_id` set) only list that store’s alternate.

### Reading mode
On a post, open **Reading mode** (or append `?reading=1`) for a 1-column, sidebar-free view. Use **Exit reading mode** to return.
Disable the link under **Display → Show Reading Mode Link**.


---

## Robots, lazy images & amphtml (v2.19)

### Meta robots
On **Content → Blog Posts → Edit**, set **Meta Robots**:
- INDEX,FOLLOW (default when empty)
- NOINDEX,FOLLOW / INDEX,NOFOLLOW / NOINDEX,NOFOLLOW

### Lazy-load images
**Display → Lazy-load Featured Images** (default Yes) adds `loading="lazy"` to featured images.

### amphtml hint
This module does **not** render AMP HTML. When you host AMP pages elsewhere:

1. Enable **SEO → Emit amphtml Link Hint**
2. Set pattern, e.g. `{base_url}amp/blog/{url_key}`

Post pages then emit `<link rel="amphtml" href="…">`.


---

## Pagination SEO, FAQ schema & CLI (v2.20)

### Pagination
When the blog list has multiple pages, the HTML head includes:
- `<link rel="prev" href="…">`
- `<link rel="next" href="…">`

### FAQ schema on list
**SEO → Emit FAQPage Schema on Blog List** (default No).
Optional **Custom FAQ JSON**:
```json
[{"question":"How do I subscribe?","answer":"Use the RSS link on the blog."}]
```
If empty, built-in generic Q&A is used.

### CLI robots
```bash
php bin/magento blogarticle:post:create --title="Hi" --content="<p>x</p>" --meta-robots=NOINDEX,FOLLOW
php bin/magento blogarticle:post:update --id=1 --meta-robots=INDEX,FOLLOW
php bin/magento blogarticle:post:show 1
```


---

## Ops: stats CLI & cache (v2.21)

```bash
php bin/magento blogarticle:stats
```

Prints posts_total, posts_enabled, comments_total, comments_pending, categories_total, tags_total, views_total.

Saving or deleting posts/comments flushes blog block cache tags (`blogarticle_post`, per-post tags).

Admin **Dashboard** shows recent posts, top viewed posts, and a pending-comment badge.


---

## FPC, grid views & body images (v2.22)

### Cache
Post/comment saves purge **block** and **full_page** cache tags for `blogarticle_post*`.

### Admin grid
**Content → Blog Posts** includes a **Views** column (from `view_count`).

### Post body images
With **Display → Lazy-load Featured Images** enabled, images inside post HTML also receive `loading="lazy"` / `decoding="async"`.
Allowed HTML now includes `img` (and figure/blockquote/code/pre).

---

## Admin thumbnail & table of contents (v2.23)

### Admin grid image
**Content → Blog Posts** shows a small **Image** thumbnail from the post’s featured image (when set).

### Table of contents
On post detail, if the body has enough `h2`–`h4` headings, an **On this page** TOC appears above the content. Headings get stable `id` attributes for anchor links.

Configure under **Stores → Configuration → Blog Article → Display**:

| Setting | Default | Notes |
|---|---|---|
| Show Table of Contents | Yes | Master switch |
| Minimum Headings for TOC | 2 | Hide TOC when fewer headings |

---

## Related posts & monthly archive (v2.24)

### Related posts
On post detail, **Related posts** prefers the same category, then posts sharing tags, then recent posts. Limit: **Display → Related Posts Limit** (default 3).

### Monthly archive
- Clean URLs: `/blog/archive/2026` (year) and `/blog/archive/2026/07` (month)
- Sidebar **Archive** lists months with counts (toggle: **Sidebar → Show Monthly Archive**)

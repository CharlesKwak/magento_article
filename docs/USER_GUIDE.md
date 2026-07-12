# User Guide

How to use **ThirdParty_BlogArticle** after it is installed on Magento 2.

Module: `ThirdParty_BlogArticle`  
Version covered: **2.1.0**

For install steps, see [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md).  
For runtime dependencies and SBOM-style inventory, see [DEPENDENCIES_AND_SBOM.md](./DEPENDENCIES_AND_SBOM.md).

---

## 1. Product overview

This module provides a **database-backed blog post list** with Admin management:

| Surface | URL / navigation | Behavior |
|---|---|---|
| Storefront list | `/blog/` (`?p=2`, `?q=keyword`) | Search + pagination |
| Storefront detail | `/blog/<url_key>` | Full article body |
| Admin | **Content → Blog Posts** | CRUD + search/status filters |
| Config | **Stores → Configuration → Third Party → Blog Article** | Posts per page |
| REST | `/rest/V1/blogarticle/posts*` | Public read + search |
| GraphQL | `/graphql` (`blogPosts`, `blogPost`) | Public read + search |

### What you can do in v2.1.0

- Search and page through the storefront blog list.
- Filter Admin posts by keyword and status.
- Integrate via REST or GraphQL.
- Configure list page size per store.

### Not available yet

- Categories, tags, authors, scheduled publish.
- Write REST/GraphQL mutations / Magento CLI for posts.
- Per-store-view content.

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

| Topic | Behavior in v2.1.0 |
|---|---|
| Multi-website / store view | No `store_id` column; **all posts show on all store views** that can reach the route |
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
A: No.

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

- Storefront: comment form on post detail (when enabled).
- Admin: **Content → Blog Comments** to approve or delete.
- Config: **Stores → Configuration → Third Party → Blog Article → Comments**.

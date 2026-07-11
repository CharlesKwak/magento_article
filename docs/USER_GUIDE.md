# User Guide

How to use **ThirdParty_BlogArticle** after it is installed on Magento 2.

Module: `ThirdParty_BlogArticle`  
Version covered: **1.0.0**

For install steps, see [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md).  
For runtime dependencies and SBOM-style inventory, see [DEPENDENCIES_AND_SBOM.md](./DEPENDENCIES_AND_SBOM.md).

---

## 1. Product overview

This module provides a **minimal, database-backed blog post list**:

| Surface | URL / navigation | Behavior |
|---|---|---|
| Storefront | `/blog/index/index` | Renders all rows from `thirdparty_blogarticle_post` |
| Admin | **Content → Blog Posts** | Renders the same list for operators |

It is intended as a **starting sample** for Magento blog-style content—not a full CMS.

### What you can do in v1.0.0

- View the full list of posts on the storefront.
- View the full list of posts in Admin.
- Control Admin menu access via ACL resource `ThirdParty_BlogArticle::posts`.

### What you cannot do in v1.0.0 (by design of current code)

- Create, edit, or delete posts from the Admin UI.
- Open a single-post detail page.
- Use categories, tags, authors, draft/publish status, or SEO URL keys.
- Call REST/GraphQL APIs for posts.
- Import posts via Magento CLI.

**Workaround for creating content:** insert rows into the database (see [§4 Managing posts](#4-managing-posts-v100)).

---

## 2. Storefront usage

### 2.1 Open the article list

1. Ensure the module is enabled and at least one post exists (see install guide).
2. In a browser, open:

   ```text
   https://<your-store-base-url>/blog/index/index
   ```

3. You should see one block per post: **title** (`<h2>`) and **content**.

There is no pagination: **all posts** are loaded.

### 2.2 How content is rendered

| Field | Escaping | Implication |
|---|---|---|
| `title` | HTML-escaped via `$block->escapeHtml()` | Safe against title XSS |
| `content` | **Not escaped** (output as stored) | Treated as HTML; **stored XSS risk** if untrusted users can write to the DB |

**Operational guidance**

- Only allow trusted operators (or trusted ETL/scripts) to insert `content`.
- Prefer plain text or carefully reviewed HTML.
- Do not expose direct DB write access to untrusted parties.

### 2.3 Empty state

If the table has zero rows, the page still loads successfully but displays **no posts**. This is expected—not a routing failure.

---

## 3. Admin usage

### 3.1 Open the list

1. Log in to Magento Admin.
2. Go to **Content → Blog Posts**.
3. Review titles and content for all posts (same data source as storefront).

Admin route frontName: `blogarticle`  
Controller: `ThirdParty\BlogArticle\Controller\Adminhtml\Post\Index`  
ACL constant: `ThirdParty_BlogArticle::posts`

### 3.2 Permissions

| Role type | Expected behavior |
|---|---|
| Administrators (full access) | Menu visible; page accessible |
| Custom role without resource | Menu hidden / access denied |
| Custom role with `ThirdParty_BlogArticle::posts` | Menu and page available |

Configure under:

**System → Permissions → User Roles → [Role] → Role Resources**

Grant **Blog Posts** (resource id `ThirdParty_BlogArticle::posts`).

### 3.3 No write UI

The Admin page is **read-only**. There is no “Add New Post”, mass action, or inline edit in v1.0.0.  
Use database operations described below until a future version adds CRUD.

---

## 4. Managing posts (v1.0.0)

### 4.1 Data model

Table: `thirdparty_blogarticle_post`  
(Respect Magento table prefix if configured, e.g. `m2_thirdparty_blogarticle_post`.)

| Column | Type (logical) | Required | Description |
|---|---|---|---|
| `post_id` | integer, PK, auto-increment | auto | Unique post id |
| `title` | string (up to 255) | yes | Display title |
| `content` | text (up to ~64KB) | yes | Body; may include HTML |
| `creation_time` | timestamp | default now | Created at |

There is no `status`, `updated_at`, `url_key`, `store_id`, or media field.

### 4.2 Create a post (SQL)

```sql
INSERT INTO thirdparty_blogarticle_post (title, content)
VALUES (
  'My first article',
  '<p>Hello from <em>ThirdParty_BlogArticle</em>.</p>'
);
```

Verify:

```sql
SELECT post_id, title, LEFT(content, 80) AS content_preview, creation_time
FROM thirdparty_blogarticle_post
ORDER BY post_id DESC;
```

Then refresh:

- Storefront: `/blog/index/index`
- Admin: **Content → Blog Posts**

Clear Magento full-page cache if enabled and content does not appear:

```bash
php bin/magento cache:flush
```

### 4.3 Update a post (SQL)

```sql
UPDATE thirdparty_blogarticle_post
SET
  title = 'Updated title',
  content = '<p>Updated body</p>'
WHERE post_id = 1;
```

### 4.4 Delete a post (SQL)

```sql
DELETE FROM thirdparty_blogarticle_post
WHERE post_id = 1;
```

### 4.5 Bulk sample data

```sql
INSERT INTO thirdparty_blogarticle_post (title, content) VALUES
  ('Welcome to the blog', '<p>This is dummy content for the first post.</p>'),
  ('Second sample post', '<p>This post is used to verify list rendering.</p>');
```

These titles match the repository fixture `tests/dummy_posts.json` (used only for repository tests—not auto-imported into Magento).

---

## 5. Day-to-day operator checklist

- [ ] Module enabled: `php bin/magento module:status ThirdParty_BlogArticle`
- [ ] Table exists and has rows for demos / UAT
- [ ] Storefront list URL bookmarked for QA
- [ ] Admin role has **Blog Posts** ACL if not using full admin
- [ ] Only trusted staff change `content` (HTML / XSS policy)
- [ ] Back up the table before bulk SQL changes in production

---

## 6. Multi-store / localization notes

| Topic | Behavior in v1.0.0 |
|---|---|
| Multi-website / store view | No `store_id` column; **all posts show on all store views** that can reach the route |
| Translation of post content | Not supported; store raw title/content per row only |
| Magento i18n for UI strings | Admin title uses `__('Blog Posts')`; no extensive phrase package |

If you need per-store content, you must extend the schema and collection filters in a future release.

---

## 7. FAQ

**Q: I installed the module but the blog page is blank.**  
A: Empty table. Insert at least one row ([§4.2](#42-create-a-post-sql)).

**Q: Where is “Add Post” in Admin?**  
A: Not implemented in v1.0.0. Use SQL (or wait for a future CRUD release).

**Q: Can customers comment on articles?**  
A: No.

**Q: Does the module index posts in Elasticsearch?**  
A: No. Listing is a direct DB collection load.

**Q: Is HTML in content sanitized?**  
A: No. Content is printed as stored. Treat it as trusted HTML only.

**Q: How do I change the URL from `/blog`?**  
A: Change the frontend route `frontName` in `etc/frontend/routes.xml` and redeploy (developer change), or add Magento URL rewrites externally. No Admin config exists in v1.0.0.

**Q: LICENSE vs composer license field?**  
A: Root `LICENSE` is GPL-2.0; `composer.json` currently says MIT. See installation guide license note.

---

## 8. Feature roadmap context (not committed)

Useful next capabilities for a production “article” product (for planning only):

1. Admin form + save/delete controllers (or UI Component grid)
2. Declarative schema (`db_schema.xml`) and Data Patch seed
3. Post detail page + `url_key` + publish status
4. Proper HTML sanitization or Magento WYSIWYG + filter
5. Pagination and Admin search

These are **not** available in 1.0.0.

---

## 9. Related documents

| Document | Purpose |
|---|---|
| [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md) | Install, enable, verify, uninstall |
| [DEPENDENCIES_AND_SBOM.md](./DEPENDENCIES_AND_SBOM.md) | Dependencies, versions, SBOM |
| [RELEASE_NOTES.md](./RELEASE_NOTES.md) | Changelog |

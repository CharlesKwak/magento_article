# User Guide

How to use **ThirdParty_BlogArticle** after it is installed on Magento 2.

Module: `ThirdParty_BlogArticle`  
Version covered: **1.2.0**

For install steps, see [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md).  
For runtime dependencies and SBOM-style inventory, see [DEPENDENCIES_AND_SBOM.md](./DEPENDENCIES_AND_SBOM.md).

---

## 1. Product overview

This module provides a **database-backed blog post list** with Admin management:

| Surface | URL / navigation | Behavior |
|---|---|---|
| Storefront list | `/blog/index/index` | Enabled posts with excerpt + link |
| Storefront detail | `/blog/post/view/url_key/<key>` or `.../id/<id>` | Full article body |
| Admin | **Content → Blog Posts** | List, create, edit, delete, enable/disable |

### What you can do in v1.2.0

- Browse the storefront list and open each article detail page.
- Create, edit, delete posts in Admin.
- Set **URL Key** and **Status** (Enabled/Disabled).
- Rely on sample posts after a fresh install (when the table was empty).
- Control Admin access via ACL `ThirdParty_BlogArticle::posts`.

### Not available yet

- Categories, tags, authors, scheduled publish.
- REST/GraphQL APIs / Magento CLI for posts.
- Per-store-view content / Magento URL Rewrite admin config.

---

## 2. Storefront usage

### 2.1 Open the article list

1. Ensure the module is enabled.
2. Open:

   ```text
   https://<your-store-base-url>/blog/index/index
   ```

3. Each **enabled** post shows title, date, excerpt, and a **Read more** link.

There is no pagination: all enabled posts are loaded.

### 2.2 Open an article detail page

```text
https://<your-store-base-url>/blog/post/view/url_key/welcome-to-the-blog
https://<your-store-base-url>/blog/post/view/id/1
```

Disabled or missing posts return Magento’s no-route (404) response.

### 2.3 How content is rendered

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

### 2.4 Empty state

If the table has zero rows, the page shows: *“No blog posts are available yet.”*

---

## 3. Admin usage

### 3.1 Open the list

1. Log in to Magento Admin.
2. Go to **Content → Blog Posts**.
3. Review the table (ID, title, created time, actions).

Admin route frontName: `blogarticle`  
ACL: `ThirdParty_BlogArticle::posts`

### 3.2 Create a post

1. Click **Add New Post**.
2. Enter **Title** (required, max 255 characters).
3. Optionally set **URL Key** (auto-generated from title if empty).
4. Set **Status** to Enabled or Disabled.
5. Enter **Content** (required; basic HTML allowed).
6. Click **Save Post** or **Save and Continue Edit**.

### 3.3 Edit a post

1. On the list, click **Edit** for a row.
2. Change title/content.
3. Save.

### 3.4 Delete a post

1. On the list (or edit form), click **Delete**.
2. Confirm the browser dialog.
3. The post is removed from the database and both Admin and storefront lists.

### 3.5 Permissions

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

| Topic | Behavior in v1.2.0 |
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

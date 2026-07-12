# Installation Guide

This guide explains how to install and verify the **ThirdParty_BlogArticle** module on a Magento 2 store from an operator’s perspective.

Module package name: `thirdparty/module-blog-article`  
Module code name: `ThirdParty_BlogArticle`  
Current version: **2.5.0**

---

## 1. What this module does

After a successful install, the module:

1. Registers itself with Magento as `ThirdParty_BlogArticle`.
2. Creates the database table `thirdparty_blogarticle_post` (if it does not exist).
3. Seeds **two sample posts** when the table is empty (data patch `AddSampleBlogPosts`).
4. Exposes a **storefront list page** at `/blog/index/index` (frontName: `blog`).
5. Exposes an **Admin list + CRUD** under **Content → Blog Posts** (Add / Edit / Delete).

### Capability matrix (v2.5.0)

| Capability | Status |
|---|---|
| List posts on storefront | Supported (enabled, **paginated**, **searchable**) |
| List posts in Admin | Supported (**search** + status filter + UI grids) |
| Create / edit / delete posts in Admin UI | **Supported** |
| Sample (seed) posts on install | **Supported** (empty table only) |
| Single-post detail page | **Supported** (`/blog/{url_key}`) |
| URL key + publish status + schedule | **Supported** |
| Categories / tags / multi-store | **Supported** |
| Comments (moderation, replies, spam, email, reCAPTCHA) | **Supported** |
| Post author + OG/JSON-LD SEO | **Supported** |
| Clean `/blog/category|tag/{url_key}` URLs | **Supported** |
| Admin mass Enable/Disable posts | **Supported** |
| CLI list/show/set-status | **Supported** (`blogarticle:post:*`) |
| REST API (read + write surfaces) | **Supported** |
| GraphQL (read + mutations + comments) | **Supported** |
| Configurable page size / comments / media | **Supported** (Admin config) |

For dependency and runtime inventory (PHP, MySQL, Magento, SBOM-style notes), see [DEPENDENCIES_AND_SBOM.md](./DEPENDENCIES_AND_SBOM.md).

---

## 2. Requirements

### 2.1 Host Magento environment

This module is **not** a standalone application. It must be installed into an existing Magento Open Source or Adobe Commerce instance.

| Component | Requirement | Notes |
|---|---|---|
| Magento Open Source / Adobe Commerce | **2.4.x** (tested target: 2.4.4+ with `magento/framework` ^103.0) | Module declares `magento/framework: ^103.0` |
| PHP | **≥ 8.1** | Match your Magento patch version’s supported PHP list |
| Database | **MySQL 8.0** (recommended) or MariaDB version supported by your Magento release | Module uses Magento DDL only; no custom SQL dialect |
| Search engine | Elasticsearch / OpenSearch as required by Magento | Not used by this module, but required by Magento itself |
| Web server | Nginx or Apache as required by Magento | Standard Magento vhost |
| Composer | 2.x recommended | For Magento and optional package-based install |

Always follow the [official Magento system requirements](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/system-requirements.html) for your exact Magento patch version. That document is authoritative for MySQL, Elasticsearch, and PHP pairings.

### 2.2 Magento PHP extensions (host)

Typical Magento 2.4.x extensions (not unique to this module) include: `bcmath`, `ctype`, `curl`, `dom`, `gd`, `hash`, `iconv`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `simplexml`, `soap`, `xsl`, `zip`.  
CI for this repository validates only a subset (`mbstring`, `intl`, `dom`) for repository unit tests.

### 2.3 Access you need

- SSH or shell access to the Magento application server
- Ability to run `php bin/magento` as the Magento file owner
- Database write access (for `setup:upgrade` and optional sample `INSERT`)
- Admin user with permission to manage roles (to grant **Blog Posts** ACL if using a restricted role)

---

## 3. Pre-install checklist

- [ ] Magento store boots successfully (storefront and Admin login work).
- [ ] PHP version is ≥ 8.1 and matches Magento’s support matrix.
- [ ] Database is MySQL 8.0 / compatible MariaDB.
- [ ] You can run `php bin/magento --version`.
- [ ] Deployment mode is known (`developer` or `production`).
- [ ] You have a backup (code + DB) if installing on a non-dev environment.

```bash
php -v
php bin/magento --version
php bin/magento deploy:mode:show
```

---

## 4. Installation methods

Choose **one** method.

### Method A — Copy into `app/code` (typical for this repository)

From this repository root, copy the module into the Magento root:

```bash
# MAGENTO_ROOT = path to your Magento installation
mkdir -p "$MAGENTO_ROOT/app/code/ThirdParty"
cp -R app/code/ThirdParty/BlogArticle "$MAGENTO_ROOT/app/code/ThirdParty/BlogArticle"
```

Expected path after copy:

```text
$MAGENTO_ROOT/app/code/ThirdParty/BlogArticle/
  registration.php
  etc/module.xml
  Setup/InstallSchema.php
  ...
```

### Method B — Composer (when published as a package)

If the package is available from your Composer repository (Packagist, GitHub Packages, private satis, etc.):

```bash
cd "$MAGENTO_ROOT"
composer require thirdparty/module-blog-article:^1.0
```

Composer autoload in this package maps:

- `files`: `app/code/ThirdParty/BlogArticle/registration.php`
- PSR-4: `ThirdParty\BlogArticle\` → module directory

> Note: Marketplace/GitHub Packages publishing may require additional auth (`composer config github-oauth.github.com …`). See the main [README](../README.md).

### Method C — Marketplace / ZIP package

If you received a ZIP built by `scripts/package_module.sh`:

1. Extract the archive.
2. Ensure `app/code/ThirdParty/BlogArticle` lands under the Magento root (same layout as Method A).
3. Continue with [§5 Enable and upgrade](#5-enable-and-upgrade).

Build a local ZIP from this repository:

```bash
./scripts/package_module.sh
# → dist/thirdparty-blog-article-1.0.0.zip
```

---

## 5. Enable and upgrade

Run all commands from **`$MAGENTO_ROOT`** as the Magento file owner.

```bash
cd "$MAGENTO_ROOT"

# Register / enable the module
php bin/magento module:enable ThirdParty_BlogArticle

# Create DB table and register setup version
php bin/magento setup:upgrade

# Optional but recommended in developer mode
php bin/magento cache:flush
```

### Production mode extra steps

If `deploy:mode:show` reports `production`:

```bash
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```

### Verify module registration

```bash
php bin/magento module:status ThirdParty_BlogArticle
# Expected: Module is enabled

php bin/magento module:status | grep BlogArticle
```

### Verify database table

Connect with your Magento DB credentials (from `app/etc/env.php`) and run:

```sql
SHOW TABLES LIKE 'thirdparty_blogarticle_post';
DESCRIBE thirdparty_blogarticle_post;
```

Expected columns:

| Column | Purpose |
|---|---|
| `post_id` | Primary key (auto increment) |
| `title` | Post title (required) |
| `url_key` | Unique slug for detail URL |
| `content` | Post body (required; may contain HTML) |
| `is_active` | 1 = enabled on storefront, 0 = hidden |
| `creation_time` | Created timestamp (default: current time) |
| `update_time` | Updated timestamp |

---

## 6. Sample data

### Automatic seed (default)

On `setup:upgrade`, data patch `ThirdParty\BlogArticle\Setup\Patch\Data\AddSampleBlogPosts` inserts **two sample posts** if the table exists and row count is **0**.

If the table already has rows (or the patch already ran), nothing is inserted again.

### Manual seed (optional)

Use Admin **Content → Blog Posts → Add New Post**, or SQL:

```sql
INSERT INTO thirdparty_blogarticle_post (title, content)
VALUES
  (
    'Welcome to the blog',
    '<p>This is the first sample article installed for verification.</p>'
  ),
  (
    'Second sample post',
    '<p>Use the Admin <strong>Content → Blog Posts</strong> page to manage posts.</p>'
  );
```

If your Magento installation uses a table prefix (e.g. `m2_`), prefix the table name accordingly.

CLI (ops / automation):

```bash
php bin/magento blogarticle:post:list
php bin/magento blogarticle:post:list --status=enabled --limit=20
php bin/magento blogarticle:post:show welcome-to-the-blog
php bin/magento blogarticle:post:set-status 1 disabled
```

There is still no create-post CLI; create/edit content via Admin UI or REST/GraphQL write APIs.

More field semantics and security notes: [User Guide](./USER_GUIDE.md).

---

## 7. Post-install verification

### 7.1 Storefront

1. Open list: `https://<your-store-base-url>/blog/` (`?p=2`, `?q=welcome`)
2. Detail: `https://<your-store-base-url>/blog/welcome-to-the-blog`
3. REST: `/rest/V1/blogarticle/posts?search=welcome`
4. GraphQL (POST `/graphql`):
   ```graphql
   { blogPosts(pageSize: 5, search: "welcome") { total_count items { title url_key } } }
   ```
5. Config: **Stores → Configuration → Third Party → Blog Article**

### 7.2 Admin

1. Log in to Magento Admin.
2. Navigate to **Content → Blog Posts**.
3. Confirm sample posts appear; open **Add New Post**, save a third post, edit and delete to verify CRUD.

### 7.3 ACL (restricted Admin roles)

The module registers resource:

- Resource ID: `ThirdParty_BlogArticle::posts`
- Menu parent: **Content** (`Magento_Backend::content`)

For a non-Administrator role:

1. **System → Permissions → User Roles → [Role] → Role Resources**
2. Allow **Blog Posts** (under the Admin tree as configured).
3. Save, re-login as that user, confirm the menu appears.

---

## 8. Uninstall / rollback

v1.0.0 does **not** implement Magento `UninstallInterface`. Manual cleanup:

```bash
cd "$MAGENTO_ROOT"

php bin/magento module:disable ThirdParty_BlogArticle
php bin/magento setup:upgrade
php bin/magento cache:flush
```

Remove code (Method A install):

```bash
rm -rf app/code/ThirdParty/BlogArticle
# If ThirdParty has no other modules:
# rmdir app/code/ThirdParty 2>/dev/null || true
```

Optional database cleanup (destructive):

```sql
DROP TABLE IF EXISTS thirdparty_blogarticle_post;
-- Also remove setup_module row if present:
DELETE FROM setup_module WHERE module = 'ThirdParty_BlogArticle';
```

Composer install cleanup:

```bash
composer remove thirdparty/module-blog-article
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

## 9. Troubleshooting

| Symptom | Likely cause | What to try |
|---|---|---|
| `Module "ThirdParty_BlogArticle" cannot be found` | Wrong path or missing `registration.php` | Confirm `app/code/ThirdParty/BlogArticle/registration.php` exists; re-run `module:enable` |
| Menu **Blog Posts** missing | Module disabled or ACL denied | `module:status`; grant `ThirdParty_BlogArticle::posts` |
| Storefront 404 on `/blog` | Cache / not enabled / wrong base URL | `cache:flush`; confirm module enabled; try `/blog/index/index` |
| Empty list on frontend & admin | No rows / seed skipped | Re-check table; use Admin **Add New Post** or [§6 SQL](#6-sample-data) |
| Admin form 404 on new/edit | Cache / generated code stale | `cache:flush`; production: `setup:di:compile` |
| Table does not exist | `setup:upgrade` not run | Re-run `setup:upgrade`; check DB user privileges |
| Production white screen after deploy | DI / static content stale | `setup:di:compile`, `setup:static-content:deploy`, `cache:flush` |
| Class not found | Autoload not refreshed | `composer dump-autoload` (Composer install) or clear generated code |
| Permission errors writing `generated/` or `var/` | Wrong file owner | Fix ownership to Magento runtime user |

Useful diagnostics:

```bash
php bin/magento module:status ThirdParty_BlogArticle
php bin/magento cache:status
php bin/magento setup:db:status
tail -n 100 var/log/system.log
tail -n 100 var/log/exception.log
```

---

## 10. Related documents

| Document | Purpose |
|---|---|
| [USER_GUIDE.md](./USER_GUIDE.md) | How to use Admin & storefront; field model; seed data |
| [DEPENDENCIES_AND_SBOM.md](./DEPENDENCIES_AND_SBOM.md) | Dependency inventory, MySQL/PHP matrix, SBOM notes |
| [RELEASE_NOTES.md](./RELEASE_NOTES.md) | Version history |
| [README.md](../README.md) | Project overview, packaging, CI |

---

## 11. License note

The repository root `LICENSE` file is **GPL-2.0**.  
From module version **1.1.0**, `composer.json` declares `"GPL-2.0-only"` to match.

# Installation Guide

This guide explains how to install and verify the **ThirdParty_BlogArticle** module on a Magento 2 store from an operator’s perspective.

Module package name: `thirdparty/module-blog-article`  
Module code name: `ThirdParty_BlogArticle`  
Current version: **1.0.0**

---

## 1. What this module does

After a successful install, the module:

1. Registers itself with Magento as `ThirdParty_BlogArticle`.
2. Creates the database table `thirdparty_blogarticle_post` (if it does not exist).
3. Exposes a **storefront list page** at `/blog/index/index` (frontName: `blog`).
4. Exposes an **Admin list page** under **Content → Blog Posts**.

### Important limitations (v1.0.0)

| Capability | Status |
|---|---|
| List posts on storefront | Supported |
| List posts in Admin | Supported |
| Create / edit / delete posts in Admin UI | **Not available** |
| Sample (seed) posts on install | **Not included** |
| Single-post detail page / SEO URL | **Not available** |

After install the table is **empty**. You must insert posts manually (see [§6 Seed sample data](#6-seed-sample-data-required-for-visible-content) and the [User Guide](./USER_GUIDE.md)).

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
| `content` | Post body (required; may contain HTML) |
| `creation_time` | Created timestamp (default: current time) |

---

## 6. Seed sample data (required for visible content)

v1.0.0 does **not** ship a Data Patch. Until you insert rows, storefront and Admin lists are empty.

### Option A — SQL (recommended for first verification)

```sql
INSERT INTO thirdparty_blogarticle_post (title, content)
VALUES
  (
    'Welcome to the blog',
    '<p>This is the first sample article installed for verification.</p>'
  ),
  (
    'Second sample post',
    '<p>Use the Admin <strong>Content → Blog Posts</strong> page to review the list.</p>'
  );
```

If your Magento installation uses a table prefix (e.g. `m2_`), prefix the table name accordingly:

```sql
-- Example with prefix m2_
INSERT INTO m2_thirdparty_blogarticle_post (title, content)
VALUES ('Welcome to the blog', '<p>Sample content</p>');
```

### Option B — Magento CLI is not provided

There is no `bin/magento blog:post:create` command in v1.0.0. Use SQL or a custom script.

More field semantics and security notes: [User Guide](./USER_GUIDE.md).

---

## 7. Post-install verification

### 7.1 Storefront

1. Open: `https://<your-store-base-url>/blog/index/index`  
   (also try `/blog/` depending on URL rewrite configuration)
2. **With seed data:** each post title and content should render.
3. **Without seed data:** page loads but shows no posts (empty loop).

### 7.2 Admin

1. Log in to Magento Admin.
2. Navigate to **Content → Blog Posts**.
3. Confirm the same posts appear as on the storefront list.

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
| Empty list on frontend & admin | No rows in table | Run [§6 seed SQL](#6-seed-sample-data-required-for-visible-content) |
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
`composer.json` currently lists `"MIT"` — treat this as a known metadata inconsistency until unified. Prefer the root `LICENSE` file for distribution compliance unless your legal team advises otherwise.

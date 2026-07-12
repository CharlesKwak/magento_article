# Dependencies & SBOM-oriented inventory

This document describes what **ThirdParty_BlogArticle** depends on, what runs in production versus development, and how an installing operator should think about MySQL, PHP, and Magento versions.

It is an **SBOM-style inventory for humans**, not a machine-generated CycloneDX/SPDX file. Generate a formal SBOM from your Magento root if compliance tooling requires it (see [§6](#6-generating-a-machine-readable-sbom)).

Module version: **2.3.0**  
Package name: `thirdparty/module-blog-article`

---

## 1. Summary for installers

| Layer | What you need |
|---|---|
| Application host | Magento Open Source or Adobe Commerce **2.4.x** |
| Language runtime | **PHP ≥ 8.1** (use the version pair Adobe documents for your Magento patch) |
| Database | **MySQL 8.0** recommended (or MariaDB version supported by that Magento patch) |
| Search / cache | Elasticsearch/OpenSearch + Redis as required by **Magento**, not by this module |
| Module third-party PHP libs at runtime | **None** beyond Magento Framework |

This module’s production footprint is essentially:

```text
Your Magento 2.4.x stack
  └── ThirdParty_BlogArticle (this code)
        └── magento/framework (^103.0)  [provided by Magento]
```

---

## 2. Compatibility matrix (declared / inferred)

> Status legend: **Declared** = written in `composer.json` or docs; **Inferred** = from Magento 2.4 conventions; **Verified** = covered by automated Magento integration tests in this repo (**none today**).

| Component | Constraint | Status | Notes |
|---|---|---|---|
| Magento / Adobe Commerce | 2.4.x (framework ^103.0) | Declared | `composer.json` → `magento/framework: ^103.0` |
| PHP | ≥ 8.1 | Declared | Upper bound not pinned; follow Magento’s matrix |
| MySQL | 8.0.x recommended | Inferred | Module uses Magento DDL types only |
| MariaDB | Magento-supported 10.x line | Inferred | No MariaDB-specific SQL |
| Elasticsearch / OpenSearch | Magento-required version | Inferred | Module does not query search engine |
| Redis | Optional/recommended by Magento | Inferred | Module has no direct Redis API use |
| RabbitMQ | Magento optional | N/A | Not used |
| Varnish | Magento optional | N/A | FPC may cache the blog page like any other |

### 2.1 Suggested pairings (operator starting point)

Always prefer Adobe’s official system requirements for your exact Magento version. The table below is a **practical starting point** for 2.4.x stores, not a certification claim.

| Magento line | PHP (typical) | MySQL (typical) | Module |
|---|---|---|---|
| 2.4.4 – 2.4.5 | 8.1 | 8.0 | Expected compatible |
| 2.4.6 | 8.1 / 8.2 | 8.0 | Expected compatible |
| 2.4.7 | 8.2 / 8.3 | 8.0 | Expected compatible (validate PHP ≤ framework support) |
| 2.3.x | 7.4 / 8.1 (varies) | 5.7 / 8.0 | **Not targeted** (`magento/framework` ^103) |

---

## 3. Software bill of materials (logical)

### 3.1 Runtime (production) — direct

| Component | Version / constraint | Supplier | License (as packaged) | Required |
|---|---|---|---|---|
| `ThirdParty_BlogArticle` source | 1.4.0 | This repository | GPL-2.0 (root `LICENSE`; `composer.json` → `GPL-2.0-only`) | Yes |
| PHP | ≥ 8.1 | php.net / distro | PHP License | Yes |
| `magento/framework` | ^103.0 | Adobe / Magento | OSL-3.0 / AFL-3.0 (Magento components) | Yes (peer via Magento) |

**Important:** `composer.lock` in this repository lists **zero** production packages (`"packages": []`).  
`magento/framework` is expected to be satisfied by the **host Magento installation**, not by installing this repo alone on a blank machine.

### 3.2 Runtime (production) — transitive / host platform

These are not Composer requires of the module, but every Magento store already includes them:

| Component | Role relative to this module |
|---|---|
| Magento application (OS/Commerce) | Hosts controllers, layout, blocks, ACL, setup |
| MySQL / MariaDB | Stores `thirdparty_blogarticle_post` |
| Web server (Nginx/Apache) | Serves storefront & Admin |
| Magento DB adapter (`pdo_mysql`) | Table create & collection load |
| Magento Adminhtml / Backend | Admin menu, ACL, backend controllers |

### 3.3 Development / CI only (`require-dev`)

Locked versions from this repository’s `composer.lock` (dev tree). **Do not deploy `vendor/` from this repo into Magento production** for module runtime—these are for repository unit tests only.

| Package | Locked version | Purpose |
|---|---|---|
| `phpunit/phpunit` | 10.5.47 | Test runner |
| `phpunit/php-code-coverage` | 10.1.16 | Coverage |
| `phpunit/php-file-iterator` | 4.1.0 | PHPUnit support |
| `phpunit/php-invoker` | 4.0.0 | PHPUnit support |
| `phpunit/php-text-template` | 3.0.1 | PHPUnit support |
| `phpunit/php-timer` | 6.0.0 | PHPUnit support |
| `myclabs/deep-copy` | 1.13.1 | Test helper |
| `nikic/php-parser` | 5.5.0 | Parser (coverage ecosystem) |
| `phar-io/manifest` | 2.0.4 | PHAR metadata |
| `phar-io/version` | 3.2.1 | Version parsing |
| `sebastian/cli-parser` | 2.0.1 | PHPUnit ecosystem |
| `sebastian/code-unit` | 2.0.0 | PHPUnit ecosystem |
| `sebastian/code-unit-reverse-lookup` | 3.0.0 | PHPUnit ecosystem |
| `sebastian/comparator` | 5.0.3 | PHPUnit ecosystem |
| `sebastian/complexity` | 3.2.0 | PHPUnit ecosystem |
| `sebastian/diff` | 5.1.1 | PHPUnit ecosystem |
| `sebastian/environment` | 6.1.0 | PHPUnit ecosystem |
| `sebastian/exporter` | 5.1.2 | PHPUnit ecosystem |
| (+ other `sebastian/*` locked with PHPUnit 10) | — | PHPUnit ecosystem |

Declared in `composer.json`:

```json
"require-dev": {
  "phpunit/phpunit": "^10"
}
```

### 3.4 Database schema artifact (data plane)

| Object | Name | Created by |
|---|---|---|
| Table | `thirdparty_blogarticle_post` | `Setup/InstallSchema.php` on `setup:upgrade` |
| Columns | `post_id`, `title`, `content`, `creation_time` | Same |
| Magento setup registry | `setup_module.module = ThirdParty_BlogArticle` | Magento setup |

No foreign keys, no views, no triggers, no stored procedures.

---

## 4. Network & external services

| Destination | Used by module at runtime? |
|---|---|
| External HTTP APIs | No |
| CDN | No |
| Telemetry / analytics | No |
| License servers | No |
| Composer / Packagist | Install-time only (if using Composer) |

---

## 5. Security & compliance notes for operators

| Topic | Note |
|---|---|
| Attack surface | Public storefront search/list/detail; public REST + GraphQL read; Admin CRUD/config behind ACL |
| XSS | Storefront `content` uses HTML allow-list escaping; still restrict Admin access to trusted staff |
| Secrets | Module introduces no new credentials or API keys |
| License | Root `LICENSE` = GPL-2.0; `composer.json` = `GPL-2.0-only` (aligned from 1.1.0) |
| PII | Default schema has no customer PII fields; content free-text may still contain personal data if authors paste it |

---

## 6. Generating a machine-readable SBOM

### 6.1 For this repository only (dev dependencies)

From the **module repository** root (after `composer install`):

```bash
composer show -t
composer licenses
```

Optional (if you install CycloneDX tooling in your environment):

```bash
# Example — requires a CycloneDX Composer plugin in that environment
composer CycloneDX:make-sbom
```

### 6.2 For a real store (recommended for compliance)

Generate SBOM from the **Magento root** that includes this module, so Magento + all modules are included:

```bash
cd "$MAGENTO_ROOT"
composer show
composer licenses
# Plus your org’s preferred SBOM scanner (Syft, Trivy, CycloneDX, etc.)
```

That inventory is what production actually runs.

---

## 7. CI environment (reference)

GitHub Actions workflow (`.github/workflows/ci.yml`) currently:

| Item | Value |
|---|---|
| Runner | `ubuntu-latest` |
| PHP | 8.1 |
| Extensions (CI) | `mbstring`, `intl`, `dom` |
| Commands | `composer install`, PHPUnit, zip `app/` |
| Deploy | Optional SCP when branch is `main` and secrets are set |

**Note for operators:** CI does **not** spin up Magento or MySQL. Green CI means repository smoke tests passed, not that Magento integration was verified.

Also note the workflow listens to branch name `main`, while this project historically uses `master` / `develop`—align branch names if you rely on CI for every push.

---

## 8. Packaging contents vs runtime

`scripts/package_module.sh` includes:

- `app/code/ThirdParty/BlogArticle`
- `composer.json`
- `README.md`
- `LICENSE`
- `docs/`

Package artifact name: `thirdparty-blog-article-2.3.0.zip`.

It does **not** ship `vendor/`, `composer.lock`, or Magento core. Target systems must already have Magento + PHP + MySQL.

---

## 9. Related documents

| Document | Purpose |
|---|---|
| [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md) | Install & verify on Magento |
| [USER_GUIDE.md](./USER_GUIDE.md) | Operate lists & manage posts via SQL |
| [RELEASE_NOTES.md](./RELEASE_NOTES.md) | Changelog |
| [README.md](../README.md) | Project overview |

# Magento Blog Article Module

![GitHub stars](https://img.shields.io/github/stars/CharlesKwak/magento_article?style=social)

Sample **Magento 2** module (`ThirdParty_BlogArticle`) that stores blog-style articles in MySQL and lists them on the storefront and in Admin.

| | |
|---|---|
| Package | `thirdparty/module-blog-article` |
| Module | `ThirdParty_BlogArticle` |
| Version | **1.0.0** (docs refresh: see [RELEASE_NOTES](docs/RELEASE_NOTES.md) 1.0.1) |
| License | See root [LICENSE](LICENSE) (**GPL-2.0**); note [composer.json](composer.json) currently lists MIT |

---

## Documentation (start here)

| Document | Audience | Contents |
|---|---|---|
| **[Installation Guide](docs/INSTALLATION_GUIDE.md)** | Installers / DevOps | Requirements, install methods, enable, seed SQL, verify, uninstall, troubleshooting |
| **[User Guide](docs/USER_GUIDE.md)** | Merchants / operators | Storefront & Admin usage, data model, how to add posts (SQL), ACL, FAQ |
| **[Dependencies & SBOM](docs/DEPENDENCIES_AND_SBOM.md)** | Security / platform | PHP / Magento / MySQL matrix, runtime vs dev deps, SBOM tips |
| **[Release Notes](docs/RELEASE_NOTES.md)** | Everyone | Changelog |

---

## Feature scope (v1.0.0)

### Included

- Database table `thirdparty_blogarticle_post` (`post_id`, `title`, `content`, `creation_time`)
- Storefront list: `/blog/index/index`
- Admin list: **Content → Blog Posts** (ACL: `ThirdParty_BlogArticle::posts`)

### Not included

- Admin create / edit / delete UI
- Sample posts on install (table starts empty)
- Single article detail page, categories, tags, draft status, REST/GraphQL

After install you **must insert posts** (SQL examples in the [User Guide](docs/USER_GUIDE.md)) or lists will be empty.

---

## Requirements (summary)

| Component | Requirement |
|---|---|
| Magento Open Source / Adobe Commerce | **2.4.x** (`magento/framework` ^103.0) |
| PHP | **≥ 8.1** |
| Database | **MySQL 8.0** recommended (or MariaDB supported by your Magento version) |
| Search / Redis | As required by Magento (not used directly by this module) |

Full matrix and SBOM-style inventory: [docs/DEPENDENCIES_AND_SBOM.md](docs/DEPENDENCIES_AND_SBOM.md).

---

## Quick install

```bash
# 1) Copy module into Magento root
mkdir -p "$MAGENTO_ROOT/app/code/ThirdParty"
cp -R app/code/ThirdParty/BlogArticle "$MAGENTO_ROOT/app/code/ThirdParty/BlogArticle"

# 2) Enable & upgrade
cd "$MAGENTO_ROOT"
php bin/magento module:enable ThirdParty_BlogArticle
php bin/magento setup:upgrade
php bin/magento cache:flush

# 3) Seed at least one post (required for visible content)
# mysql ... -e "INSERT INTO thirdparty_blogarticle_post (title, content) VALUES ('Hello', '<p>World</p>');"
```

**Verify**

- Storefront: `https://<store>/blog/index/index`
- Admin: **Content → Blog Posts**

Detailed steps, production mode, Composer/ZIP methods, and troubleshooting: **[Installation Guide](docs/INSTALLATION_GUIDE.md)**.

---

## Module overview

The module is registered as `ThirdParty_BlogArticle` under namespace `ThirdParty\BlogArticle`. On `setup:upgrade` it creates `thirdparty_blogarticle_post` and loads all rows via Magento collections for frontend and Admin list templates.

---

## Marketplace submission checklist

- [ ] Developer Portal account/company profile completed *(manual, outside repository)*
- [ ] Listing created (Extension vs App selected correctly) *(manual, outside repository)*
- [x] `composer.json` distribution metadata reinforced
- [x] Installation / user / release / dependency documentation prepared
- [x] ZIP packaging script and compatibility definition added
- [ ] Technical Review submitted *(manual, Seller Portal action)*
- [ ] Marketing Review submitted *(manual, Seller Portal action)*
- [ ] Feedback reflected and published *(manual, Seller Portal action)*

### Submission assets

- [docs/INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md)
- [docs/USER_GUIDE.md](docs/USER_GUIDE.md)
- [docs/DEPENDENCIES_AND_SBOM.md](docs/DEPENDENCIES_AND_SBOM.md)
- [docs/RELEASE_NOTES.md](docs/RELEASE_NOTES.md)
- ZIP packaging: `scripts/package_module.sh`

### Create ZIP package

```bash
./scripts/package_module.sh
# → dist/thirdparty-blog-article-1.0.0.zip
```

### PDF conversion for Marketplace upload

```bash
pandoc docs/INSTALLATION_GUIDE.md -o docs/INSTALLATION_GUIDE.pdf
pandoc docs/USER_GUIDE.md -o docs/USER_GUIDE.pdf
pandoc docs/DEPENDENCIES_AND_SBOM.md -o docs/DEPENDENCIES_AND_SBOM.pdf
pandoc docs/RELEASE_NOTES.md -o docs/RELEASE_NOTES.pdf
```

---

## Testing (repository)

These tests run **without** a full Magento instance (smoke checks only).

```bash
composer install
./vendor/bin/phpunit --configuration tests/phpunit.xml
```

Dummy JSON fixture test:

```bash
./tests/run_with_dummy_data.sh tests/DummyDataInputTest.php tests/dummy_posts.json
```

---

## Publishing to GitHub Packages

1. Generate a personal access token (PAT) with `write:packages` and `read:packages`.
2. Configure Composer:

   ```bash
   composer config --global github-oauth.github.com YOUR_TOKEN
   ```

3. Set package metadata in `composer.json` and push a version tag. The package appears under the repository **Packages** tab.

---

## CI/CD

GitHub Actions workflow installs Composer dev dependencies, runs PHPUnit, packages `app/`, and optionally deploys over SSH when configured.

Secrets for deploy: `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_PATH`, `DEPLOY_SSH_KEY`.

See [Dependencies & SBOM — CI section](docs/DEPENDENCIES_AND_SBOM.md#7-ci-environment-reference) for environment details and branch-name caveats.

---

## Contributing

If you find this project useful, please give it a star and consider contributing with pull requests or issues.

## License

GPL-2.0 (see [LICENSE](LICENSE)). Align `composer.json` license field before redistribution if your process requires a single SPDX identifier.

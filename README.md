# Magento Blog Article Module

![GitHub stars](https://img.shields.io/github/stars/CharlesKwak/magento_article?style=social)

Sample **Magento 2** module (`ThirdParty_BlogArticle`) that stores blog-style articles in MySQL, seeds sample content, and supports Admin CRUD plus a storefront list.

| | |
|---|---|
| Package | `thirdparty/module-blog-article` |
| Module | `ThirdParty_BlogArticle` |
| Version | **2.1.0** |
| License | [GPL-2.0](LICENSE) (`composer.json`: `GPL-2.0-only`) |

---

## Documentation

| Document | Audience | Contents |
|---|---|---|
| **[Installation Guide](docs/INSTALLATION_GUIDE.md)** | Installers / DevOps | Requirements, install, seed behavior, verify, uninstall |
| **[User Guide](docs/USER_GUIDE.md)** | Merchants / operators | Storefront, Admin CRUD, data model, ACL, FAQ |
| **[Dependencies & SBOM](docs/DEPENDENCIES_AND_SBOM.md)** | Security / platform | PHP / Magento / MySQL matrix, runtime vs dev deps |
| **[Release Notes](docs/RELEASE_NOTES.md)** | Everyone | Changelog |

---

## Feature scope (v2.1.0)

### Included

- DB table `thirdparty_blogarticle_post` (+ url_key, is_active, timestamps)
- Sample seed posts when table is empty
- Storefront: list (**search** + **pagination**), detail `/blog/<url_key>`
- Admin CRUD, search/status filters, configurable page size
- REST + **GraphQL** read APIs (search supported)
- ACL: `ThirdParty_BlogArticle::posts`, `::config`

### Not included

- Categories, tags, write APIs, per-store content

---

## Requirements (summary)

| Component | Requirement |
|---|---|
| Magento Open Source / Adobe Commerce | **2.4.x** (`magento/framework` ^103.0) |
| PHP | **≥ 8.1** |
| Database | **MySQL 8.0** recommended (or MariaDB supported by your Magento version) |
| Search / Redis | As required by Magento (not used directly by this module) |

Full matrix: [docs/DEPENDENCIES_AND_SBOM.md](docs/DEPENDENCIES_AND_SBOM.md).

---

## Quick install

```bash
# 1) Copy module into Magento root
mkdir -p "$MAGENTO_ROOT/app/code/ThirdParty"
cp -R app/code/ThirdParty/BlogArticle "$MAGENTO_ROOT/app/code/ThirdParty/BlogArticle"

# 2) Enable & upgrade (creates table + sample posts if empty)
cd "$MAGENTO_ROOT"
php bin/magento module:enable ThirdParty_BlogArticle
php bin/magento setup:upgrade
php bin/magento cache:flush
```

**Verify**

- Storefront: `https://<store>/blog/?q=welcome`
- Detail: `https://<store>/blog/welcome-to-the-blog`
- REST: `/rest/V1/blogarticle/posts?search=welcome`
- GraphQL: `{ blogPosts { total_count items { title } } }`
- Admin: **Content → Blog Posts** + **Stores → Configuration → Third Party → Blog Article**

Details: **[Installation Guide](docs/INSTALLATION_GUIDE.md)**.

---

## Module overview

Registered as `ThirdParty_BlogArticle` (`ThirdParty\BlogArticle`). On `setup:upgrade` it creates `thirdparty_blogarticle_post`, may seed sample rows, and serves collections to frontend and Admin templates. Admin write path uses dedicated New / Edit / Save / Delete controllers.

---

## Marketplace submission checklist

- [ ] Developer Portal account/company profile completed *(manual)*
- [ ] Listing created *(manual)*
- [x] `composer.json` distribution metadata reinforced
- [x] Installation / user / release / dependency documentation prepared
- [x] ZIP packaging script and compatibility definition added
- [ ] Technical Review submitted *(manual)*
- [ ] Marketing Review submitted *(manual)*
- [ ] Feedback reflected and published *(manual)*

### Create ZIP package

```bash
./scripts/package_module.sh
# → dist/thirdparty-blog-article-2.1.0.zip
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

Smoke tests only (no full Magento instance):

```bash
composer install
./vendor/bin/phpunit --configuration tests/phpunit.xml
./tests/run_with_dummy_data.sh tests/DummyDataInputTest.php tests/dummy_posts.json
```

---

## Publishing to GitHub Packages

1. PAT with `write:packages` and `read:packages`.
2. `composer config --global github-oauth.github.com YOUR_TOKEN`
3. Push a version tag; package appears under **Packages**.

---

## CI/CD

GitHub Actions: Composer install, PHPUnit, zip `app/`, optional SSH deploy via secrets `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_PATH`, `DEPLOY_SSH_KEY`.

---

## Contributing

Stars and pull requests are welcome.

## License

GPL-2.0 — see [LICENSE](LICENSE).

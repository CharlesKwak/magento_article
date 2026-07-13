# Packagist / Composer install

## Maintainer checklist

- [ ] `composer.json` name/version/license/autoload correct
- [ ] Public GitHub repo
- [ ] Git tag `vX.Y.Z` pushed (matches composer version)
- [ ] Submit on packagist.org
- [ ] Enable auto-update webhook
- [ ] Verify `composer require thirdparty/module-blog-article`
- [ ] GitHub Topics set
- [ ] Optional Marketplace ZIP: `./scripts/package_module.sh`


Package: **`thirdparty/module-blog-article`**  
Repository: https://github.com/CharlesKwak/magento_article  
Current version: **2.19.0**

## 1. Publish on Packagist (maintainer)

1. Log in at [packagist.org](https://packagist.org).
2. **Submit** → paste `https://github.com/CharlesKwak/magento_article`.
3. Enable GitHub Service Hook / auto-update so tags sync.
4. Create a Git tag for releases, e.g. `v2.19.0`:

```bash
git tag -a v2.19.0 -m "2.19.0"
git push origin v2.19.0
```

`composer.json` at the repo root already declares:

- `"type": "magento2-module"`
- autoload PSR-4 → `app/code/ThirdParty/BlogArticle/`
- `registration.php` via `files` autoload

## 2. Install via Composer (store owner)

### A) From Packagist (after publish)

```bash
cd "$MAGENTO_ROOT"
composer require thirdparty/module-blog-article
php bin/magento module:enable ThirdParty_BlogArticle
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### B) From GitHub VCS (works today without Packagist)

In Magento root `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/CharlesKwak/magento_article"
    }
  ]
}
```

Then:

```bash
composer require thirdparty/module-blog-article:dev-main
# or a tag once released: thirdparty/module-blog-article:^2.12
php bin/magento module:enable ThirdParty_BlogArticle
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### C) Path repository (local clone)

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "/absolute/path/to/magento_article",
      "options": { "symlink": true }
    }
  ]
}
```

```bash
composer require thirdparty/module-blog-article:@dev
```

## 3. ZIP package (Marketplace / manual)

```bash
./scripts/package_module.sh
# → dist/thirdparty-blog-article-2.19.0.zip  (module under app/code + docs)
./scripts/package_module.sh --module-root
# → dist/thirdparty-blog-article-module-2.19.0.zip  (Composer-style module root)
```

## 4. GitHub topics (discoverability)

Suggested repository topics:

`magento2` `magento2-module` `magento2-extension` `magento2-blog` `blog` `graphql` `adobe-commerce` `open-source` `magento2-extension-free`

Set under GitHub → Settings → General → Topics (or `gh repo edit --add-topic …`).

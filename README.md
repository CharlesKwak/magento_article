# Magento Blog Article Module
This repository contains a sample Magento 2 module that adds basic blog article functionality with a simple database-backed post table.

## Installation
1. Copy the `app` directory into your Magento installation root.
2. Run `php bin/magento setup:upgrade` to register the module and create the blog table.

After installation, navigate to `/blog/index/index` in your store frontend to view the posts stored in the database.

## Module Overview
The module is registered under the name `ThirdParty_BlogArticle` and provides a starting point for building blog features in a Magento store. It creates a `thirdparty_blogarticle_post` table on installation and displays all posts using the `Article` block.

## Testing
Install dependencies with Composer and run the PHPUnit suite:

```bash
composer install
./vendor/bin/phpunit --configuration tests/phpunit.xml
```

## Publishing to GitHub Packages
1. Generate a personal access token (PAT) with `write:packages` and `read:packages` permissions.
2. Configure Composer to use your PAT:
   ```bash
   composer config --global github-oauth.github.com YOUR_TOKEN
   ```
3. Set a package name in `composer.json` and push a version tag to GitHub.
   The package will appear under the repository's **Packages** tab.

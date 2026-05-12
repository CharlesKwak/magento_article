# Magento Blog Article Module
This repository contains a sample Magento 2 module that adds basic blog article functionality with a simple database-backed post table.

## Installation
1. Copy the `app` directory into your Magento installation root.
2. Run `php bin/magento setup:upgrade` to register the module and create the blog table.

After installation, navigate to `/blog/index/index` in your store frontend to view the posts stored in the database.
An admin listing page is available under **Content > Blog Posts** in the Magento backend.

## Module Overview
The module is registered under the name `ThirdParty_BlogArticle` and provides a starting point for building blog features in a Magento store. It creates a `thirdparty_blogarticle_post` table on installation and displays all posts using the `Article` block.

## Marketplace Submission Checklist (Status)
- [ ] Developer Portal account/company profile completed *(manual, outside repository)*
- [ ] Listing created (Extension vs App selected correctly) *(manual, outside repository)*
- [x] `composer.json` distribution metadata reinforced
- [x] Installation / user / release documentation prepared (draft markdown files included)
- [x] ZIP packaging script and compatibility definition added
- [ ] Technical Review submitted *(manual, Seller Portal action)*
- [ ] Marketing Review submitted *(manual, Seller Portal action)*
- [ ] Feedback reflected and published *(manual, Seller Portal action)*

## Submission Assets in this Repository
- Installation guide draft: `docs/INSTALLATION_GUIDE.md`
- User guide draft: `docs/USER_GUIDE.md`
- Release notes draft: `docs/RELEASE_NOTES.md`
- ZIP packaging script: `scripts/package_module.sh`

### Create ZIP package
```bash
./scripts/package_module.sh
```

### PDF conversion for Marketplace upload
Marketplace often requests PDF-formatted docs. Convert the markdown files into PDF using your preferred tool (for example, Pandoc):
```bash
pandoc docs/INSTALLATION_GUIDE.md -o docs/INSTALLATION_GUIDE.pdf
pandoc docs/USER_GUIDE.md -o docs/USER_GUIDE.pdf
pandoc docs/RELEASE_NOTES.md -o docs/RELEASE_NOTES.pdf
```

## Testing
Install dependencies with Composer and run the PHPUnit suite:

```bash
composer install
./vendor/bin/phpunit --configuration tests/phpunit.xml
```

Run a specific PHPUnit test file with a dummy JSON input file:

```bash
./tests/run_with_dummy_data.sh tests/DummyDataInputTest.php tests/dummy_posts.json
```

`run_with_dummy_data.sh` passes the JSON file path through the `DUMMY_DATA_FILE` environment variable so your test can load fixture-like dummy input dynamically.

## Publishing to GitHub Packages
1. Generate a personal access token (PAT) with `write:packages` and `read:packages` permissions.
2. Configure Composer to use your PAT:
   ```bash
   composer config --global github-oauth.github.com YOUR_TOKEN
   ```
3. Set a package name in `composer.json` and push a version tag to GitHub.
   The package will appear under the repository's **Packages** tab.

## CI/CD Pipeline
This repository ships with a GitHub Actions workflow that installs dependencies, runs the unit tests, packages the `app` directory and optionally deploys it using SSH.
To enable deployment set the `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_PATH` and `DEPLOY_SSH_KEY` secrets in your repository settings.

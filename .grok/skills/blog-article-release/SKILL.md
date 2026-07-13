---
name: blog-article-release
description: >
  Release and packaging workflow for Magento Blog Article: version bump, release
  notes, GEO corpus sync, ZIP package, Marketplace checklist. Triggers: release,
  version bump, package zip, marketplace package, /blog-article-release.
metadata:
  short-description: "Release Magento Blog Article"
---

# Blog Article — Release skill

## Preflight

1. Apply `/blog-article-core`.
2. Working tree clean enough to reason about version (warn if dirty).
3. Confirm target version (semver). Example current: **2.9.0**.

## Version bump targets

Update **all** of:

- [ ] `composer.json` → `version`
- [ ] `app/code/ThirdParty/BlogArticle/etc/module.xml` → `setup_version`
- [ ] `README.md` version badges/tables
- [ ] `docs/INSTALLATION_GUIDE.md`, `docs/USER_GUIDE.md` headers if they pin version
- [ ] `docs/RELEASE_NOTES.md` new section (Added/Changed/Upgrade notes)
- [ ] GEO: `llms.txt`, `docs/geo/ENTITY.md`, `FAQ.md`, `ANSWERS.md`, `COMPARISON.md` as needed
- [ ] Any package script defaults that embed version

## Quality gates

```bash
composer install
./vendor/bin/phpunit --configuration tests/phpunit.xml
./scripts/package_module.sh
# expect dist/thirdparty-blog-article-X.Y.Z.zip
```

## Marketplace packaging (if requested)

```bash
pandoc docs/INSTALLATION_GUIDE.md -o docs/INSTALLATION_GUIDE.pdf
pandoc docs/USER_GUIDE.md -o docs/USER_GUIDE.pdf
pandoc docs/DEPENDENCIES_AND_SBOM.md -o docs/DEPENDENCIES_AND_SBOM.pdf
pandoc docs/RELEASE_NOTES.md -o docs/RELEASE_NOTES.pdf
```

Do **not** mark Marketplace portal steps as done unless the user completed them manually.

## Git

Only commit/tag/push when the user explicitly asks. Prefer conventional commit:

`chore(release): X.Y.Z` or `feat: … (X.Y.Z)` matching project history.

## GEO after release

Run `/blog-article-geo-aeo` checklist so AI-facing docs stay citation-accurate.

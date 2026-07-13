---
name: blog-article-test
description: >
  Write and run tests for Magento Blog Article. Use when adding PHPUnit smoke
  tests, dummy data tests, or verifying CLI/API file presence. Triggers: add
  tests, run phpunit, test coverage, /blog-article-test. Pair with test-writer.
metadata:
  short-description: "Test Magento Blog Article"
---

# Blog Article — Test skill

## Reality check

This repository’s `tests/` suite is primarily **smoke / structural** (file presence, generators, dummy data). It does **not** boot full Magento. Do not invent Magento integration tests that cannot run here without a full instance.

## Setup

1. Apply `/blog-article-core`.
2. Read `tests/phpunit.xml` and a neighboring `*Test.php` as the template.
3. Prefer extending the closest existing test class pattern.

## Patterns

| Pattern | Examples |
|---|---|
| File existence for features | `AdminCrudFilesTest`, `CliAndMassStatusFilesTest` |
| Generator unit logic | `UrlKeyGeneratorTest` |
| Dummy JSON input | `DummyDataInputTest` + `dummy_posts.json` |
| License / module xml | `LicenseTest`, `ModuleXmlTest` |

## When adding a feature, add tests that

1. Assert new critical PHP/XML/phtml files exist (if that suite style is used).
2. Cover pure PHP helpers with real assertions when cheap.
3. Avoid requiring `Magento\Framework\App\Bootstrap`.

## Commands

```bash
composer install
./vendor/bin/phpunit --configuration tests/phpunit.xml
./tests/run_with_dummy_data.sh tests/DummyDataInputTest.php tests/dummy_posts.json
```

## Output

- What was tested
- Command results
- Gaps that need a full Magento env (call them out explicitly)

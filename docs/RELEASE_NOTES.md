# Release Notes

## 1.0.1 - 2026-07-12 (documentation)

Documentation-only release. Module runtime code unchanged from 1.0.0.

### Added
- Expanded installation guide: requirements, install methods, seed data, production steps, uninstall, troubleshooting
- Expanded user guide: operator workflows, data model, SQL CRUD workaround, ACL, XSS notes, FAQ
- New `docs/DEPENDENCIES_AND_SBOM.md`: compatibility matrix, runtime/dev inventory, MySQL/PHP notes, SBOM generation tips

### Changed
- README reorganized for installer-first reading with links to all docs

### Known limitations (unchanged from 1.0.0)
- No Admin UI to create/edit/delete posts
- No sample Data Patch on install
- No single-post detail page
- `content` field rendered without HTML escaping
- License metadata mismatch between root `LICENSE` (GPL-2.0) and `composer.json` (MIT)

---

## 1.0.0 - 2026-05-12

- Added marketplace-ready package metadata in `composer.json`
- Added submission-oriented documentation set:
  - Installation guide (draft)
  - User guide (draft)
  - Release notes
- Added package script for ZIP artifact generation
- Module features:
  - DB table `thirdparty_blogarticle_post`
  - Storefront list at `/blog/index/index`
  - Admin list under **Content → Blog Posts**

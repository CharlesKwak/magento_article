# Release Notes

## 1.3.0 - 2026-07-12

### Added
- Storefront **pagination** on the blog list (`?p=2`, 5 posts per page by default)
- **Clean URLs** via custom router: `/blog/{url_key}` → post detail
- **REST API** (anonymous read, enabled posts only):
  - `GET /rest/V1/blogarticle/posts?page=1&pageSize=10`
  - `GET /rest/V1/blogarticle/posts/:postId`
  - `GET /rest/V1/blogarticle/posts/url/:urlKey`
- Service contracts: `PostRepositoryInterface`, `PostInterface`

### Changed
- List/detail/Admin “View” links prefer `/blog/{url_key}`
- Module / package version **1.3.0**

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
# production:
php bin/magento setup:di:compile
```
No database schema change from 1.2.0 → 1.3.0 (code/config only).

---

## 1.2.0 - 2026-07-12

### Added
- Storefront **detail page**: `/blog/post/view/url_key/<key>` (or `id/<post_id>`)
- List page links with excerpt + **Read more**
- Schema fields: `url_key` (unique), `is_active`, `update_time`
- Schema patch `AddUrlKeyAndStatusColumns` + data patch `BackfillUrlKeysAndStatus`
- Admin form fields: URL Key, Status (Enabled/Disabled)
- Admin list columns: URL Key, Status, storefront **View** link
- `UrlKeyGenerator` for unique slug generation from title
- Frontend shows **enabled posts only**

### Changed
- Module / package version **1.2.0**
- InstallSchema creates full column set for new installs

### Upgrade notes
```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```
Existing posts receive auto-generated `url_key` values; `is_active` defaults to enabled.

---

## 1.1.0 - 2026-07-12

### Added
- Admin CRUD for blog posts:
  - **Content → Blog Posts** list with Add / Edit / Delete
  - New & Edit form (title, content)
  - Save / Save and Continue Edit
- Data patch `AddSampleBlogPosts`: seeds two sample posts when the table is empty
- Frontend empty-state message and safer content rendering (allowed HTML tags only)
- `PostFactory` for Admin controllers/blocks

### Changed
- Module setup version `1.0.0` → `1.1.0`
- Package version `1.1.0`; package ZIP name updated
- `composer.json` license field aligned to **GPL-2.0-only** (matches root `LICENSE`)
- Installation / user / dependency docs updated for CRUD and seed behavior

### Security
- Storefront `content` is filtered through `escapeHtml()` with a limited allow-list (`p`, `br`, `em`, `strong`, `a`, lists, headings)

---

## 1.0.1 - 2026-07-12 (documentation)

Documentation-only release. Module runtime code unchanged from 1.0.0.

### Added
- Expanded installation guide: requirements, install methods, seed data, production steps, uninstall, troubleshooting
- Expanded user guide: operator workflows, data model, SQL CRUD workaround, ACL, XSS notes, FAQ
- New `docs/DEPENDENCIES_AND_SBOM.md`: compatibility matrix, runtime/dev inventory, MySQL/PHP notes, SBOM generation tips

### Changed
- README reorganized for installer-first reading with links to all docs

### Known limitations (as of 1.0.x)
- No Admin UI to create/edit/delete posts *(resolved in 1.1.0)*
- No sample Data Patch on install *(resolved in 1.1.0)*
- No single-post detail page
- License metadata mismatch *(resolved in 1.1.0 composer field)*

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

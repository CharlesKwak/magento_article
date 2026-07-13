# Migration Guide — Importing posts into ThirdParty_BlogArticle

This guide explains how to move blog content **into** `ThirdParty_BlogArticle` using the built-in **CSV import** (`blogarticle:post:import`). It is intentionally lightweight: no proprietary WordPress plugin dependency.

Module version: **2.23.0**

---

## 1. Recommended path: CSV

### Sample columns

Required:

- `title`
- `content`

Optional:

- `url_key`, `author`, `status` (`enabled` / `disabled`), `excerpt`
- `category_id`, `tag_ids` (comma-separated tag IDs)
- `store_id`, `published_at`
- `meta_title`, `meta_description`, `featured_image`

See [samples/posts_import_sample.csv](./samples/posts_import_sample.csv).

### Import commands

```bash
# Native Blog Article columns
php bin/magento blogarticle:post:import /path/to/posts.csv --dry-run

# WordPress-style column names (post_title, post_content, post_name, …)
php bin/magento blogarticle:post:import /path/to/wp-export.csv --format=wordpress --dry-run
php bin/magento blogarticle:post:import /path/to/wp-export.csv --format=wordpress --update
```

Sample WP-style CSV: [samples/wordpress_posts_import_sample.csv](./samples/wordpress_posts_import_sample.csv).

### Import commands (detail)

```bash
# Dry run (validate only)
php bin/magento blogarticle:post:import /path/to/posts.csv --dry-run

# Create new rows
php bin/magento blogarticle:post:import /path/to/posts.csv

# Upsert by url_key
php bin/magento blogarticle:post:import /path/to/posts.csv --update
```

Export (round-trip):

```bash
php bin/magento blogarticle:post:export --status=enabled
# → var/export/blogarticle_posts_*.csv
```

---

## 2. From WordPress

1. In WordPress: **Tools → Export → Posts** (or use a CSV export plugin).
2. Map fields:

| WordPress | Blog Article CSV |
|---|---|
| post_title | `title` |
| post_content (HTML) | `content` |
| post_name | `url_key` |
| post_author display name | `author` |
| post_excerpt | `excerpt` |
| post_date_gmt | `published_at` |
| post_status = publish | `status=enabled` |
| categories (first) | create category in Admin → use `category_id` |
| tags | create tags → use `tag_ids` as IDs |
| featured image URL | `featured_image` (media path or URL Magento can resolve) |
| Yoast/RankMath title | `meta_title` |
| Yoast/RankMath description | `meta_description` |

3. Run import with `--dry-run`, fix rows, then import.
4. Re-upload images into Magento media if needed; update `featured_image` paths.

---

## 3. From Mageplaza / Magefan / other Magento blogs

Typical approach:

1. Export source tables (`mageplaza_blog_post`, etc.) via MySQL or Admin if available.
2. Map to the CSV columns above.
3. Categories/tags: create equivalents first, then fill `category_id` / `tag_ids`.
4. Product links (v2.11+): after posts exist, re-enter **Related Product SKUs** in Admin or extend CSV later.

Product-to-post links live in `thirdparty_blogarticle_post_product` (post_id, product_id).

---

## 4. Verification checklist

- [ ] Storefront `/blog/` lists imported posts  
- [ ] Detail URLs `/blog/{url_key}` open  
- [ ] Category/tag filters work  
- [ ] Author pages `/blog/author/{slug}` (slug = author name with spaces → hyphens)  
- [ ] GraphQL `{ blogPosts { items { title url_key } } }`  
- [ ] Export CSV and re-import with `--update` (round-trip)

---

## 5. What this module does **not** auto-import

- Full WordPress media library / attachment IDs  
- Nested comment threads from third-party engines (Disqus/Facebook)  
- Mageplaza Topics / post attributes / traffic history  

Use CSV + Admin for those, or open a contribution if you need a dedicated importer class.


---

## Comment import (v2.13+)

```bash
php bin/magento blogarticle:comment:import /path/to/comments.csv --dry-run
```

Required columns: `author_name`, `content`, and either `post_id` or `post_url_key`.  
Optional: `is_approved`, `parent_id`, `author_email`, `comment_id` (with `--update`).

Sample: [samples/comments_import_sample.csv](./samples/comments_import_sample.csv).

# Screenshots

PNG previews linked from the project [README](../../README.md).

| File | Description |
|---|---|
| `storefront-list.png` | Storefront blog index (search, taxonomy, post cards) |
| `storefront-post.png` | Post detail (share, related, comments) |
| `admin-posts.png` | Magento Admin Blog Posts grid |

## Regenerate

Mockups live in `html/`. From the repository root, with Google Chrome installed on macOS:

```bash
CHROME="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
ROOT="docs/screenshots"
HTML="$ROOT/html"

"$CHROME" --headless=new --disable-gpu --hide-scrollbars --window-size=1440,1100 \
  --screenshot="$ROOT/storefront-list.png" "file://$PWD/$HTML/storefront-list.html"

"$CHROME" --headless=new --disable-gpu --hide-scrollbars --window-size=1200,1400 \
  --screenshot="$ROOT/storefront-post.png" "file://$PWD/$HTML/storefront-post.html"

"$CHROME" --headless=new --disable-gpu --hide-scrollbars --window-size=1440,900 \
  --screenshot="$ROOT/admin-posts.png" "file://$PWD/$HTML/admin-posts.html"
```

These images are **illustrative** (styled to resemble Luma storefront / Magento Admin). They are not captured from a live Magento instance.

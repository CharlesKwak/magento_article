#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
OUT_DIR="$ROOT_DIR/dist"
PKG_NAME="thirdparty-blog-article-1.7.0"
PKG_PATH="$OUT_DIR/${PKG_NAME}.zip"

mkdir -p "$OUT_DIR"
rm -f "$PKG_PATH"

(
  cd "$ROOT_DIR"
  zip -r "$PKG_PATH" \
    app/code/ThirdParty/BlogArticle \
    composer.json \
    README.md \
    LICENSE \
    docs
)

echo "Created package: $PKG_PATH"

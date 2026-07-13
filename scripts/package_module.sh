#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
OUT_DIR="$ROOT_DIR/dist"
VERSION="$(php -r 'echo json_decode(file_get_contents($argv[1]), true)["version"] ?? "0.0.0";' "$ROOT_DIR/composer.json")"
MODE="app-code"
if [[ "${1:-}" == "--module-root" ]]; then
  MODE="module-root"
fi

mkdir -p "$OUT_DIR"

if [[ "$MODE" == "module-root" ]]; then
  PKG_NAME="thirdparty-blog-article-module-${VERSION}"
  PKG_PATH="$OUT_DIR/${PKG_NAME}.zip"
  rm -f "$PKG_PATH"
  STAGE="$(mktemp -d)"
  trap 'rm -rf "$STAGE"' EXIT
  # Flatten module to zip root for Marketplace-style installs / alternate packaging
  cp -R "$ROOT_DIR/app/code/ThirdParty/BlogArticle/." "$STAGE/"
  # Provide a composer.json that maps PSR-4 to this flattened root
  php -r '
    $src = json_decode(file_get_contents($argv[1]), true);
    $src["autoload"] = [
      "files" => ["registration.php"],
      "psr-4" => ["ThirdParty\\BlogArticle\\" => ""]
    ];
    file_put_contents($argv[2], json_encode($src, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) . "\n");
  ' "$ROOT_DIR/composer.json" "$STAGE/composer.json"
  cp "$ROOT_DIR/LICENSE" "$STAGE/" 2>/dev/null || true
  (
    cd "$STAGE"
    zip -r "$PKG_PATH" .
  )
  echo "Created module-root package: $PKG_PATH"
else
  PKG_NAME="thirdparty-blog-article-${VERSION}"
  PKG_PATH="$OUT_DIR/${PKG_NAME}.zip"
  rm -f "$PKG_PATH"
  (
    cd "$ROOT_DIR"
    zip -r "$PKG_PATH" \
      app/code/ThirdParty/BlogArticle \
      composer.json \
      README.md \
      LICENSE \
      docs \
      llms.txt
  )
  echo "Created app/code package: $PKG_PATH"
fi

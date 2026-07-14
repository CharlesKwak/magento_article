# Search Autocomplete — Design & Implementation Plan

Status: **planned, not implemented**. This document is the working plan for adding
search autocomplete (type-ahead suggestions) to the blog search inputs. Written
2026-07-15; implementation scheduled for a follow-up session.

---

## 1. Goal & scope

When a shopper types into a blog search box (the list-page search bar and the
sidebar search widget), show a small dropdown of matching post titles after 2+
characters, updating as they type. Selecting a suggestion navigates directly to
that post; pressing Enter still submits the normal `/blog?q=…` search.

In scope:

- A JSON suggest endpoint on the storefront.
- A lightweight, dependency-free frontend widget wired to both search inputs.
- An optional GraphQL query for headless storefronts.

Out of scope (explicitly):

- Admin-side search autocomplete.
- Fuzzy matching / search-engine (OpenSearch) integration — suggestions come
  from a simple SQL `LIKE` over post titles, same as the existing list search.
- Search-term analytics or popularity ranking.

## 2. Current state (why this is a new feature)

- Blog search is a plain GET form: `article.phtml` and `sidebar/search.phtml`
  submit `q` to `/blog`, filtered server-side by `PostFilter::applySearch()`.
- The module ships **no frontend JavaScript** (`view/frontend/web/` contains
  only CSS) and no suggest/typeahead endpoint in REST, GraphQL, or controllers.

## 3. Architecture

One shared service owns the suggestion logic; two thin transports expose it.

```
                    ┌────────────────────────────┐
  storefront JS ──▶ │ Controller/Search/Suggest   │──┐
  (GET /blog/       │ (JSON result, no layout)    │  │   ┌──────────────────────┐
   search/suggest)  └────────────────────────────┘  ├──▶│ Model/SearchSuggest   │
                    ┌────────────────────────────┐  │   │ - query(q, limit)     │
  GraphQL client ──▶│ Resolver/PostSuggest        │──┘   │ - PostFilter reuse    │
  (blogPostSuggest) │ (optional, phase 2)         │      │ - PostVisibility rules│
                    └────────────────────────────┘      └──────────────────────┘
```

### 3.1 `Model/SearchSuggest` (new)

- `suggest(string $query, int $limit = 8, ?int $storeId = null): array`
- Returns `[['title' => ..., 'url_key' => ..., 'url' => ...], …]`.
- Reuses the exact same gates as the list page so suggestions can never leak
  hidden content: `PostFilter::applySearch()` + `applyActiveOnly()` +
  `applyPublishedOnly()` + `applyStoreId()`; select only `title`/`url_key`
  columns; `setPageSize($limit)`; order by newest (or `applyDefaultSort`).
- Input hygiene: trim, require `mb_strlen ≥ 2`, cap at 64 chars, clamp limit
  to 1–10. Empty/short queries return `[]` without touching the DB.

### 3.2 `Controller/Search/Suggest` (new)

- Route: `GET /blog/search/suggest?q=…` — resolves through the **standard
  router** (frontName `blog`, controller `search`, action `suggest`), so no
  custom-router work is needed; two-segment paths already fall through
  `Controller/Router` untouched.
- **Required guard:** add `'search'` to `Controller\Router::RESERVED` so a
  post with url_key `search` can never shadow the endpoint (mirrors the
  existing `rss`/`comment` entries).
- Implements `HttpGetActionInterface`; returns
  `Magento\Framework\Controller\Result\Json` —
  `{"items":[{"title":"…","url":"https://…/blog/…"}]}`. No layout, so FPC
  does not cache it; if fronted by Varnish, document that the path may be
  cached briefly (responses contain only public data, so a short TTL is safe).
- Respects the module's comments-independent config: hide behind a new
  `blogarticle/list/autocomplete_enabled` flag (default `1`) in `config.xml` +
  `system.xml` so merchants can switch it off.

### 3.3 Frontend widget (new)

- `view/frontend/web/js/search-suggest.js` — a small vanilla-JS AMD module
  (registered via `requirejs`, attached with `data-mage-init` on both search
  inputs in `article.phtml` and `sidebar/search.phtml`).
- Behavior: debounce 250 ms; abort in-flight fetch on new input
  (`AbortController`); render a `<ul role="listbox">` under the input;
  ArrowUp/ArrowDown/Enter/Escape keyboard support; click outside closes;
  ARIA combobox attributes (`aria-expanded`, `aria-activedescendant`) for
  accessibility. Suggestion text is inserted with `textContent` (never
  `innerHTML`) so titles cannot inject markup.
- Styling: extend the existing `view/frontend/web/css` with a
  `blogarticle-suggest.css`, loaded from the layout XML like the TOC css.
- New user-facing strings ("No matching posts", "Search suggestions") go into
  `i18n/en_US.csv` + `i18n/ko_KR.csv` (both dictionaries are kept complete as
  of commit 76f5845).

### 3.4 GraphQL (phase 2, optional)

- `blogPostSuggest(q: String!, limit: Int = 8): [BlogPostSuggestion]` with
  `{ title, url_key }`, resolver delegating to `Model/SearchSuggest`. Public
  (anonymous) like the other read queries. Ship only if a headless consumer
  needs it; the JSON controller covers the Luma storefront.

## 4. Implementation steps (ordered)

1. `Model/SearchSuggest` + unit-testable normalization (query trim/length/limit).
2. Add `'search'` to `Controller\Router::RESERVED`.
3. `Controller/Search/Suggest` returning JSON; wire the
   `autocomplete_enabled` config flag (config.xml, system.xml, Model/Config).
4. Frontend: JS widget + CSS + `data-mage-init` hooks in the two templates;
   requirejs registration.
5. i18n additions (en_US, ko_KR).
6. (Optional) GraphQL query + resolver + schema.
7. Docs: USER_GUIDE section + RELEASE_NOTES entry; version bump per repo
   convention.

Estimated size: ~6 new files, ~3 edited files, no DB schema changes.

## 5. Testing plan

### 5.1 Standalone suite (repo convention, runs in CI)

- `tests/Unit/SearchSuggestTest.php` — normalization rules with the existing
  Magento stubs: short query → `[]`, limit clamping, long-input truncation.
- `tests/Smoke/SearchSuggestFilesTest.php` — file-content wiring assertions in
  the style of the other Smoke tests: controller exists and implements
  `HttpGetActionInterface`; `'search'` present in Router `RESERVED`; templates
  reference the JS widget; config flag present in `config.xml`; i18n entries
  present in both CSVs.

### 5.2 Runtime verification on the local instance (magento-local)

Sync → `cache:flush`, then:

| Check | Expectation |
| --- | --- |
| `curl '/blog/search/suggest?q=welcome'` | 200 JSON, items include "Welcome to our blog" |
| `q=w` (1 char) / missing `q` | 200 `{"items":[]}`, no DB query |
| Disabled post / future `published_at` post title | never suggested (visibility gate) |
| Store-scoped post from another store | not suggested |
| `q` with `%`, `_`, quotes, `<script>` | 200, safely escaped LIKE, JSON-encoded output |
| Config flag off + flush | endpoint returns `{"items":[]}` (or 404) and inputs get no widget |
| Browser: type in both search boxes | dropdown appears, keyboard nav works, Enter still submits `/blog?q=…` |
| A post with url_key `search` created via CLI | `/blog/search/suggest` still resolves to the endpoint |

### 5.3 Regression sweep

- Existing suite (95 tests) stays green.
- `/blog`, post view, category/tag/author pages unchanged (the widget only
  attaches to search inputs).
- `setup:di:compile` clean (new constructor injections).

## 6. Risks & notes

- **LIKE-on-title performance** is bounded: `title` search is what the list
  page already does per request; suggest adds the same query with `LIMIT 8`.
  If it ever matters, add an index on `title` or move to OpenSearch — out of
  scope now.
- **Local dev gotcha** (see memory/session notes): after constructor changes,
  clear `generated/metadata` + `generated/code` on magento-local or DI throws
  "argument not passed" fatals from stale compiled metadata.
- The two search inputs currently render with plain HTML attributes; keep the
  no-JS fallback working — the widget is progressive enhancement only.

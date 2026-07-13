---
name: blog-article-geo-aeo
description: >
  GEO (Generative Engine Optimization) and AEO (Answer Engine Optimization) for
  Magento Blog Article. Use when updating llms.txt, docs/geo, FAQ, entity cards,
  README answer blocks, citation snippets, or AI-discoverable project docs.
  Prefer GEO/AEO over classic SEO keywords. Triggers: GEO, AEO, AI SEO,
  llms.txt, answer engine, /blog-article-geo-aeo.
metadata:
  short-description: "GEO/AEO content for Magento Blog Article"
---

# Blog Article — GEO / AEO skill

Optimize project documentation so **AI systems and answer engines** can retrieve, trust, and cite this module correctly. This is **not** classic keyword SEO.

## Definitions

| Term | Meaning here |
|---|---|
| **GEO** | Structure facts so generative models cite accurate entity names, version, license, and capabilities. |
| **AEO** | Provide direct question→answer units that can be returned as complete answers. |

## Corpus (edit these)

| File | Role |
|---|---|
| `llms.txt` | Crawl index for AI |
| `docs/geo/ENTITY.md` | Canonical entity card |
| `docs/geo/FAQ.md` | Q→A pages |
| `docs/geo/ANSWERS.md` | Atomic quotable claims |
| `docs/geo/COMPARISON.md` | Neutral positioning |
| `docs/geo/README.md` | Maintainer rules |
| `README.md` | Human landing with answer-first sections |
| `AGENTS.md` | Agent-facing rules |

## Writing rules

1. **Answer first** — first 1–3 sentences fully answer the question.
2. **Self-contained** — include module + package names inside answers.
3. **Entity consistency** — always `ThirdParty_BlogArticle` + `thirdparty/module-blog-article`.
4. **No keyword stuffing** — no density games; clarity wins.
5. **Verifiable** — every capability claim must exist in code or release notes.
6. **Negative claims** — document non-goals (standalone app? no).
7. **Version sync** — bump version strings together on release.
8. **Citation snippet** — keep a copy-ready block in ENTITY and COMPARISON.
9. **Stable headings** — FAQ headings should be real user questions.
10. **English default** for GEO corpus (global retrieval); add KO only if maintained.

## Anti-patterns

- Long adjective marketing without facts
- “Best Magento blog 2026” style claims without evidence
- Invented star/download/Marketplace metrics
- Duplicate contradictory version numbers

## Workflow when product changes

1. Confirm behavior in code.
2. Update `docs/RELEASE_NOTES.md`.
3. Patch ANSWERS / FAQ / ENTITY / `llms.txt` / README feature list.
4. Grep for old version string and align.

## Optional storefront note

Module already emits OG/Twitter/JSON-LD for **blog posts** (`seo.phtml`). That is on-site SEO for merchants’ articles. This skill’s primary job is **project-level GEO/AEO** (GitHub/docs discoverability for AI), unless the user asks to change storefront markup.

# GEO & AEO corpus

This folder is the **Generative Engine Optimization (GEO)** and **Answer Engine Optimization (AEO)** source of truth for the Magento Blog Article module.

## Goals

| Goal | How this corpus helps |
|---|---|
| **GEO** | Give LLMs (ChatGPT, Claude, Gemini, Perplexity, Grok, etc.) a clean entity definition and citable facts so the project is described accurately when users ask about Magento blog modules. |
| **AEO** | Provide direct question→answer pairs that answer engines can quote as complete answers, not keyword-ranked snippets. |

Classic SEO keyword lists are **out of scope** here. Prefer clarity, structure, and verifiable claims.

## Files

| File | Purpose |
|---|---|
| [ENTITY.md](./ENTITY.md) | Canonical product entity card (names, version, requirements, surfaces) |
| [FAQ.md](./FAQ.md) | Full Q&A pages for common merchant/developer questions |
| [ANSWERS.md](./ANSWERS.md) | One-line / short-paragraph atomic facts |
| [COMPARISON.md](./COMPARISON.md) | Honest positioning vs paid Magento blog extensions |
| Root [llms.txt](../../llms.txt) | Crawlable index for AI systems |
| Root [AGENTS.md](../../AGENTS.md) | Instructions for coding agents in this repo |

## Maintenance rules

1. After a user-visible release, update **version** in: `composer.json`, `module.xml`, README, ENTITY, FAQ header, ANSWERS, `llms.txt`, RELEASE_NOTES.
2. Never claim features not present in `app/code/ThirdParty/BlogArticle/`.
3. Keep answers **self-contained** (package name + module name inside the answer).
4. Prefer English for public GEO/AEO pages (global AI retrieval); add localized pages only if deliberately maintained.

## Agent skill

Use project skill `/blog-article-geo-aeo` when editing this corpus.

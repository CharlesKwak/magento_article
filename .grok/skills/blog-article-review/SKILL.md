---
name: blog-article-review
description: >
  Code review for Magento Blog Article changes. Use when reviewing diffs, PRs,
  or local commits for bugs, Magento convention violations, ACL/XSS, GraphQL
  consistency, and GEO doc accuracy. Triggers: review, code review, PR review,
  /blog-article-review. Pair with reviewer agent.
metadata:
  short-description: "Review Magento Blog Article changes"
---

# Blog Article — Review skill

Review only **real, high-confidence** issues. Prefer severity over nit volume.

## Setup

1. Apply `/blog-article-core` and `AGENTS.md`.
2. Determine scope: unstaged diff, branch, or specified PR.
3. Read surrounding Magento patterns, not only the hunk.

## Checklist

### Correctness

- Repository/collection filters match multi-store and status rules
- URL key uniqueness / generation edge cases
- Scheduled publish / `is_active` behavior consistent across storefront + API
- CSV import validation and `--update` semantics

### Magento conventions

- ACL on Admin actions; CSRF where Magento expects form keys
- `module.xml` sequence for used modules
- Layout handles and UI component naming
- DI preferences instead of hard `ObjectManager` in new code

### Security

- XSS: templates escape user/admin content
- Mass assignment / GraphQL input validation
- Comment spam / reCAPTCHA paths not bypassable
- No secrets in repo

### API surface

- `schema.graphqls` ↔ resolver classes
- `webapi.xml` routes ↔ service contracts
- Backward compatibility of fields

### Tests & docs

- Smoke tests updated for new files/commands when pattern exists
- User-visible changes reflected in RELEASE_NOTES
- Public capability claims match `docs/geo/ANSWERS.md` (flag mismatches)

## Output format

For each issue (confidence ≥ 80 only):

```markdown
### Issue — Severity: bug|security|regression|gap
- File: path:line
- Confidence: 80–100
- Problem: ...
- Evidence: ...
- Fix: ...
```

End with summary: blocking vs non-blocking.

## Do not fail for

- Pure style nits not in AGENTS.md
- Hypothetical refactors unrelated to the change

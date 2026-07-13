---
name: blog-article-security
description: >
  Security review for Magento Blog Article: XSS, ACL, mass assignment, comment
  spam, reCAPTCHA bypass, GraphQL auth, file upload, CSV injection. Triggers:
  security audit, vuln review, /blog-article-security. Pair with security-auditor.
metadata:
  short-description: "Security audit Magento Blog Article"
---

# Blog Article — Security skill

## Scope hotspots

| Area | Paths / signals |
|---|---|
| Storefront XSS | `view/frontend/templates/**/*.phtml`, comment rendering |
| Admin XSS | admin templates, grid actions |
| Comments | `CommentSpamGuard`, `RecaptchaValidator`, `Controller/Comment` |
| GraphQL auth | `Model/GraphQl/Authorization.php`, mutations in schema |
| REST ACL | `etc/webapi.xml`, resource refs |
| Admin ACL | `etc/acl.xml`, Adminhtml controllers `_isAllowed` |
| Uploads | `FeaturedImageUploader`, media gallery paths |
| CSV | import path traversal, formula injection in exports |
| Email | `CommentNotifier`, templates under `view/frontend/email` |
| Cron | `Cron/`, scheduled publish cache flush |

## Process

1. Apply `/blog-article-core`.
2. Trace untrusted input → storage → output for the feature under review.
3. Verify authorization on every write path (Admin, REST, GraphQL mutation).
4. Confirm escaping on every template echo of post/comment/author fields.
5. Check mass actions and delete endpoints for CSRF/form key + ACL.
6. Report only reproducible or high-confidence findings.

## Severity labels

- **critical** — unauth RCE/file write, auth bypass to Admin, stored XSS wide blast
- **high** — privilege escalation, spam flood without control, sensitive data leak
- **medium** — missing escape in less-visited template, weak validation
- **low** — defense-in-depth gaps

## Output

Structured findings with file:line, exploit scenario (conceptual, no weaponized exploit), and fix guidance. No exploit PoC payloads.

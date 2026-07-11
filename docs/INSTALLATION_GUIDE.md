# Installation Guide (Marketplace Submission Draft)

## Requirements
- Adobe Commerce / Magento Open Source 2.4.x
- PHP 8.1+

## Install from package
1. Copy module files into Magento root under `app/code/ThirdParty/BlogArticle`.
2. Run:
   ```bash
   php bin/magento setup:upgrade
   php bin/magento cache:flush
   ```

## Verify
- Frontend: `/blog/index/index`
- Admin: `Content > Blog Posts`

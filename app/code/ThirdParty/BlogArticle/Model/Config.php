<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_BLOG_NAME = 'blogarticle/general/blog_name';
    public const XML_PATH_SHOW_TOP_MENU = 'blogarticle/general/show_top_menu';
    public const XML_PATH_SHOW_FOOTER_LINK = 'blogarticle/general/show_footer_link';
    public const XML_PATH_PAGE_SIZE = 'blogarticle/list/page_size';
    public const XML_PATH_LIST_META_TITLE = 'blogarticle/seo/list_meta_title';
    public const XML_PATH_LIST_META_DESCRIPTION = 'blogarticle/seo/list_meta_description';
    public const XML_PATH_SIDEBAR_ENABLED = 'blogarticle/sidebar/enabled';
    public const XML_PATH_SIDEBAR_RECENT_COUNT = 'blogarticle/sidebar/recent_count';
    public const XML_PATH_SIDEBAR_SHOW_SEARCH = 'blogarticle/sidebar/show_search';
    public const XML_PATH_COMMENTS_ENABLED = 'blogarticle/comments/enabled';
    public const XML_PATH_COMMENTS_AUTO_APPROVE = 'blogarticle/comments/auto_approve';
    public const XML_PATH_COMMENTS_SPAM = 'blogarticle/comments/spam_protection';
    public const XML_PATH_COMMENTS_MIN_SECONDS = 'blogarticle/comments/min_submit_seconds';
    public const XML_PATH_COMMENTS_NOTIFY = 'blogarticle/comments/notify_enabled';
    public const XML_PATH_COMMENTS_NOTIFY_EMAIL = 'blogarticle/comments/notify_email';
    public const XML_PATH_RECAPTCHA_ENABLED = 'blogarticle/comments/recaptcha_enabled';
    public const XML_PATH_RECAPTCHA_SITE_KEY = 'blogarticle/comments/recaptcha_site_key';
    public const XML_PATH_RECAPTCHA_SECRET_KEY = 'blogarticle/comments/recaptcha_secret_key';
    public const XML_PATH_RECAPTCHA_MIN_SCORE = 'blogarticle/comments/recaptcha_min_score';
    public const XML_PATH_UPLOAD_MAX_KB = 'blogarticle/media/max_upload_kb';

    private $scopeConfig;
    private $encryptor;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    public function getBlogName(?int $storeId = null): string
    {
        $name = trim((string) $this->scopeConfig->getValue(
            self::XML_PATH_BLOG_NAME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
        return $name !== '' ? $name : (string) __('Blog');
    }

    public function isShowTopMenu(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHOW_TOP_MENU,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isShowFooterLink(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHOW_FOOTER_LINK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPageSize(?int $storeId = null): int
    {
        $size = (int) $this->scopeConfig->getValue(
            self::XML_PATH_PAGE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($size < 1) {
            return 5;
        }
        return min(50, $size);
    }

    public function getListMetaTitle(?int $storeId = null): string
    {
        return trim((string) $this->scopeConfig->getValue(
            self::XML_PATH_LIST_META_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
    }

    public function getListMetaDescription(?int $storeId = null): string
    {
        return trim((string) $this->scopeConfig->getValue(
            self::XML_PATH_LIST_META_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
    }

    public function isSidebarEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SIDEBAR_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSidebarRecentCount(?int $storeId = null): int
    {
        $n = (int) $this->scopeConfig->getValue(
            self::XML_PATH_SIDEBAR_RECENT_COUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($n < 1) {
            return 5;
        }
        return min(20, $n);
    }

    public function isSidebarSearchEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SIDEBAR_SHOW_SEARCH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isCommentsEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENTS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isCommentsAutoApprove(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENTS_AUTO_APPROVE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isCommentSpamProtectionEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENTS_SPAM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCommentMinSubmitSeconds(?int $storeId = null): int
    {
        $v = (int) $this->scopeConfig->getValue(
            self::XML_PATH_COMMENTS_MIN_SECONDS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return max(0, min(120, $v));
    }

    public function isCommentNotifyEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENTS_NOTIFY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCommentNotifyEmail(?int $storeId = null): string
    {
        $email = trim((string) $this->scopeConfig->getValue(
            self::XML_PATH_COMMENTS_NOTIFY_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
        if ($email !== '') {
            return $email;
        }
        return trim((string) $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
    }

    public function isRecaptchaEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_RECAPTCHA_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getRecaptchaSiteKey(?int $storeId = null): string
    {
        return trim((string) $this->scopeConfig->getValue(
            self::XML_PATH_RECAPTCHA_SITE_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
    }

    public function getRecaptchaSecretKey(?int $storeId = null): string
    {
        $value = trim((string) $this->scopeConfig->getValue(
            self::XML_PATH_RECAPTCHA_SECRET_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
        if ($value === '') {
            return '';
        }
        // Encrypted backend stores ciphertext; decrypt when possible
        try {
            $decrypted = $this->encryptor->decrypt($value);
            if (is_string($decrypted) && $decrypted !== '') {
                return trim($decrypted);
            }
        } catch (\Exception $e) {
            // fall through to raw value
        }
        return $value;
    }

    public function getRecaptchaMinScore(?int $storeId = null): float
    {
        $score = (float) $this->scopeConfig->getValue(
            self::XML_PATH_RECAPTCHA_MIN_SCORE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($score <= 0 || $score > 1) {
            return 0.5;
        }
        return $score;
    }

    public function getMaxUploadKb(?int $storeId = null): int
    {
        $kb = (int) $this->scopeConfig->getValue(
            self::XML_PATH_UPLOAD_MAX_KB,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($kb < 100) {
            return 2048;
        }
        return min(10240, $kb);
    }
}

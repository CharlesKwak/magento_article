<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_PAGE_SIZE = 'blogarticle/list/page_size';
    public const XML_PATH_COMMENTS_ENABLED = 'blogarticle/comments/enabled';
    public const XML_PATH_COMMENTS_AUTO_APPROVE = 'blogarticle/comments/auto_approve';
    public const XML_PATH_COMMENTS_SPAM = 'blogarticle/comments/spam_protection';
    public const XML_PATH_COMMENTS_MIN_SECONDS = 'blogarticle/comments/min_submit_seconds';
    public const XML_PATH_COMMENTS_NOTIFY = 'blogarticle/comments/notify_enabled';
    public const XML_PATH_COMMENTS_NOTIFY_EMAIL = 'blogarticle/comments/notify_email';
    public const XML_PATH_UPLOAD_MAX_KB = 'blogarticle/media/max_upload_kb';

    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
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
        // Fall back to general contact email
        return trim((string) $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
    }

    /**
     * Max featured image upload size in kilobytes.
     */
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

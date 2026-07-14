<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Lightweight spam checks: honeypot, minimum form fill time, basic flood control.
 */
class CommentSpamGuard
{
    public const HONEYPOT_FIELD = 'website_url';
    public const TIMESTAMP_FIELD = 'form_ts';

    private Config $config;
    private RemoteAddress $remoteAddress;
    private DateTime $dateTime;

    /** @var array simple per-request memory; flood uses DB-less file cache optional - use timestamp session */
    private static $recentIps = [];

    public function __construct(
        Config $config,
        RemoteAddress $remoteAddress,
        DateTime $dateTime
    ) {
        $this->config = $config;
        $this->remoteAddress = $remoteAddress;
        $this->dateTime = $dateTime;
    }

    /**
     * @param array $params Request params
     * @return void
     * @throws LocalizedException
     */
    public function assertNotSpam(array $params): void
    {
        if (!$this->config->isCommentSpamProtectionEnabled()) {
            return;
        }

        // Honeypot: bots fill hidden field
        $honeypot = trim((string) ($params[self::HONEYPOT_FIELD] ?? ''));
        if ($honeypot !== '') {
            throw new LocalizedException(__('Unable to submit comment.'));
        }

        // Minimum seconds between form render and submit
        $minSeconds = $this->config->getCommentMinSubmitSeconds();
        $formTs = (int) ($params[self::TIMESTAMP_FIELD] ?? 0);
        $now = $this->dateTime->gmtTimestamp();
        if ($formTs > 0 && $minSeconds > 0 && ($now - $formTs) < $minSeconds) {
            throw new LocalizedException(__('Please wait a moment before submitting your comment.'));
        }
        // Reject absurd future/old timestamps (replay)
        if ($formTs > 0 && ($formTs > $now + 60 || $formTs < $now - 86400)) {
            throw new LocalizedException(__('Unable to submit comment.'));
        }

        // Very light same-IP flood guard within this PHP process (best-effort)
        $ip = (string) $this->remoteAddress->getRemoteAddress();
        if ($ip !== '') {
            $bucket = self::$recentIps[$ip] ?? 0;
            if ($bucket >= 5) {
                throw new LocalizedException(__('Too many comments. Please try again later.'));
            }
            self::$recentIps[$ip] = $bucket + 1;
        }

        // Block obvious link spam in content when too many URLs
        $content = (string) ($params['content'] ?? '');
        if (preg_match_all('#https?://#i', $content, $m) && count($m[0]) > 3) {
            throw new LocalizedException(__('Your comment contains too many links.'));
        }
    }
}

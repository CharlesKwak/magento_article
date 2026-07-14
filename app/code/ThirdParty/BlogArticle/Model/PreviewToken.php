<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\DeploymentConfig;

/**
 * Time-limited HMAC tokens for storefront draft / scheduled post previews.
 *
 * Query param: preview={base64url(postId|expiry|sig)}
 */
class PreviewToken
{
    private DeploymentConfig $deploymentConfig;
    private Config $config;

    public function __construct(
        DeploymentConfig $deploymentConfig,
        Config $config
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->config = $config;
    }

    /**
     * @param int $postId
     * @param int|null $ttlSeconds Override TTL (default from config hours)
     */
    public function create(int $postId, ?int $ttlSeconds = null): string
    {
        if ($postId <= 0) {
            return '';
        }
        if ($ttlSeconds === null || $ttlSeconds < 60) {
            $ttlSeconds = $this->config->getPreviewTokenTtlSeconds();
        }
        $expiry = time() + $ttlSeconds;
        $payload = $postId . '|' . $expiry;
        $sig = hash_hmac('sha256', $payload, $this->getSecret());
        return $this->base64UrlEncode($payload . '|' . $sig);
    }

    public function isValid(string $token, int $postId): bool
    {
        if ($token === '' || $postId <= 0) {
            return false;
        }
        $raw = $this->base64UrlDecode($token);
        if ($raw === '' || substr_count($raw, '|') < 2) {
            return false;
        }
        $parts = explode('|', $raw);
        if (count($parts) < 3) {
            return false;
        }
        $id = (int) $parts[0];
        $expiry = (int) $parts[1];
        $sig = (string) $parts[2];
        if ($id !== $postId || $expiry < time()) {
            return false;
        }
        $payload = $id . '|' . $expiry;
        $expected = hash_hmac('sha256', $payload, $this->getSecret());
        return hash_equals($expected, $sig);
    }

    private function getSecret(): string
    {
        try {
            $crypt = (string) $this->deploymentConfig->get('crypt/key');
            if ($crypt !== '') {
                return hash('sha256', 'blogarticle.preview.' . $crypt, true);
            }
        } catch (\Throwable $e) {
            // fall through
        }
        // Fallback when deployment config unavailable (e.g. isolated tests)
        return hash('sha256', 'blogarticle.preview.fallback', true);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $value = strtr($value, '-_', '+/');
        $pad = strlen($value) % 4;
        if ($pad > 0) {
            $value .= str_repeat('=', 4 - $pad);
        }
        $decoded = base64_decode($value, true);
        return is_string($decoded) ? $decoded : '';
    }
}

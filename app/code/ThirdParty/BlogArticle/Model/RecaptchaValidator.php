<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Psr\Log\LoggerInterface;

/**
 * Optional Google reCAPTCHA v2/v3 token verification.
 */
class RecaptchaValidator
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private $config;
    private $curl;
    private $remoteAddress;
    private $logger;

    public function __construct(
        Config $config,
        Curl $curl,
        RemoteAddress $remoteAddress,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->curl = $curl;
        $this->remoteAddress = $remoteAddress;
        $this->logger = $logger;
    }

    /**
     * @param string|null $token
     * @return void
     * @throws LocalizedException
     */
    public function assertValid(?string $token): void
    {
        if (!$this->config->isRecaptchaEnabled()) {
            return;
        }

        $secret = $this->config->getRecaptchaSecretKey();
        if ($secret === '') {
            // Misconfiguration: fail closed when enabled without secret
            throw new LocalizedException(__('reCAPTCHA is not configured correctly.'));
        }

        $token = trim((string) $token);
        if ($token === '') {
            throw new LocalizedException(__('Please complete the reCAPTCHA challenge.'));
        }

        try {
            $this->curl->post(self::VERIFY_URL, [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => (string) $this->remoteAddress->getRemoteAddress(),
            ]);
            $body = $this->curl->getBody();
            $result = json_decode($body, true);
            if (!is_array($result) || empty($result['success'])) {
                throw new LocalizedException(__('reCAPTCHA verification failed. Please try again.'));
            }
            // v3 score threshold
            if (isset($result['score'])) {
                $min = $this->config->getRecaptchaMinScore();
                if ((float) $result['score'] < $min) {
                    throw new LocalizedException(__('reCAPTCHA score too low. Please try again.'));
                }
            }
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('BlogArticle reCAPTCHA error: ' . $e->getMessage());
            throw new LocalizedException(__('Unable to verify reCAPTCHA. Please try again later.'));
        }
    }
}

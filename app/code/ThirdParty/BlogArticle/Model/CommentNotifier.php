<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use ThirdParty\BlogArticle\Api\Data\CommentInterface;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;

/**
 * Sends admin notification email for new comments.
 */
class CommentNotifier
{
    public const XML_TEMPLATE_ID = 'blogarticle_comment_notification';

    private $config;
    private $transportBuilder;
    private $inlineTranslation;
    private $storeManager;
    private $postRepository;
    private $logger;

    public function __construct(
        Config $config,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        PostRepositoryInterface $postRepository,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->postRepository = $postRepository;
        $this->logger = $logger;
    }

    /**
     * @param CommentInterface $comment
     * @return void
     */
    public function notify(CommentInterface $comment): void
    {
        if (!$this->config->isCommentNotifyEnabled()) {
            return;
        }
        $to = $this->config->getCommentNotifyEmail();
        if ($to === '') {
            return;
        }

        try {
            $postTitle = '';
            try {
                $post = $this->postRepository->getById((int) $comment->getPostId(), false);
                $postTitle = (string) $post->getTitle();
            } catch (\Exception $e) {
                $postTitle = (string) __('Post #%1', $comment->getPostId());
            }

            $store = $this->storeManager->getStore();
            $this->inlineTranslation->suspend();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier(self::XML_TEMPLATE_ID)
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => $store->getId() ?: Store::DEFAULT_STORE_ID,
                ])
                ->setTemplateVars([
                    'post_title' => $postTitle,
                    'post_id' => (int) $comment->getPostId(),
                    'author_name' => (string) $comment->getAuthorName(),
                    'author_email' => (string) $comment->getAuthorEmail(),
                    'comment_content' => (string) $comment->getContent(),
                    'is_approved' => (int) $comment->getIsApproved() ? __('Yes') : __('No (pending)'),
                    'store_name' => $store->getName(),
                ])
                ->setFromByScope('general')
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->logger->error('BlogArticle comment notification failed: ' . $e->getMessage());
        }
    }
}

<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\PreviewToken;

/**
 * bin/magento blogarticle:post:preview-url --id=12
 */
class PostPreviewUrlCommand extends Command
{
    private PostFactory $postFactory;
    private PreviewToken $previewToken;
    private StoreManagerInterface $storeManager;

    public function __construct(
        PostFactory $postFactory,
        PreviewToken $previewToken,
        StoreManagerInterface $storeManager,
        ?string $name = null
    ) {
        $this->postFactory = $postFactory;
        $this->previewToken = $previewToken;
        $this->storeManager = $storeManager;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:post:preview-url')
            ->setDescription(
                'Print storefront URL for a post; appends a signed preview token when draft or scheduled'
            )
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Post ID')
            ->addOption('url-key', null, InputOption::VALUE_REQUIRED, 'Post URL key (alternative to --id)')
            ->addOption(
                'force-token',
                null,
                InputOption::VALUE_NONE,
                'Always append a preview token even if the post is public'
            )
            ->addOption(
                'store',
                null,
                InputOption::VALUE_REQUIRED,
                'Store ID for base URL (default: post store or default store)'
            )
            ->addOption(
                'ttl',
                null,
                InputOption::VALUE_REQUIRED,
                'Token TTL in seconds (default from config hours)'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (int) $input->getOption('id');
        $urlKeyOpt = trim((string) $input->getOption('url-key'));
        $post = $this->postFactory->create();
        if ($id > 0) {
            $post->load($id);
        } elseif ($urlKeyOpt !== '') {
            $post->load($urlKeyOpt, 'url_key');
        } else {
            $output->writeln('<error>--id or --url-key is required</error>');
            return Command::FAILURE;
        }
        if (!$post->getId()) {
            $output->writeln('<error>Post not found.</error>');
            return Command::FAILURE;
        }

        $urlKey = trim((string) $post->getUrlKey());
        if ($urlKey === '') {
            $output->writeln('<error>Post has no url_key.</error>');
            return Command::FAILURE;
        }

        try {
            $storeIdOpt = $input->getOption('store');
            if ($storeIdOpt !== null && $storeIdOpt !== '') {
                $store = $this->storeManager->getStore((int) $storeIdOpt);
            } else {
                $postStore = $post->getStoreId() ? (int) $post->getStoreId() : 0;
                if ($postStore > 0) {
                    $store = $this->storeManager->getStore($postStore);
                } else {
                    $store = $this->storeManager->getDefaultStoreView()
                        ?: $this->storeManager->getStore();
                }
            }
            $base = rtrim($store->getBaseUrl(), '/');
            $url = $base . '/blog/' . ltrim($urlKey, '/');

            $needsToken = (bool) $input->getOption('force-token')
                || !(int) $post->getIsActive()
                || $this->isScheduledFuture($post);

            if ($needsToken) {
                $ttl = $input->getOption('ttl');
                $ttlSeconds = $ttl !== null && $ttl !== '' ? (int) $ttl : null;
                $token = $this->previewToken->create((int) $post->getId(), $ttlSeconds);
                if ($token !== '') {
                    $url .= '?preview=' . rawurlencode($token);
                }
            }

            $output->writeln($url);
            $output->writeln(sprintf(
                '<info>post_id=%d is_active=%d url_key=%s token=%s</info>',
                (int) $post->getId(),
                (int) $post->getIsActive(),
                $urlKey,
                $needsToken ? 'yes' : 'no'
            ));
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }

    private function isScheduledFuture($post): bool
    {
        $publishedAt = $post->getPublishedAt();
        if (!$publishedAt) {
            return false;
        }
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->getTimestamp();
        $pub = strtotime((string) $publishedAt . ' UTC');
        return $pub && $pub > $now;
    }
}

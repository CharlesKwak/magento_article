<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\PostTagLink;

/**
 * bin/magento blogarticle:post:show <id|url_key>
 */
class PostShowCommand extends Command
{
    private const ARG_IDENTIFIER = 'identifier';

    private PostFactory $postFactory;
    private PostTagLink $postTagLink;

    public function __construct(
        PostFactory $postFactory,
        PostTagLink $postTagLink,
        ?string $name = null
    ) {
        $this->postFactory = $postFactory;
        $this->postTagLink = $postTagLink;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:post:show')
            ->setDescription('Show details for a blog post by ID or URL key')
            ->addArgument(
                self::ARG_IDENTIFIER,
                InputArgument::REQUIRED,
                'Post ID or url_key'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifier = trim((string) $input->getArgument(self::ARG_IDENTIFIER));
        if ($identifier === '') {
            $output->writeln('<error>Identifier is required.</error>');
            return Command::FAILURE;
        }

        $post = $this->postFactory->create();
        if (ctype_digit($identifier)) {
            $post->load((int) $identifier);
        } else {
            $post->load($identifier, 'url_key');
        }

        if (!$post->getId()) {
            $output->writeln(sprintf('<error>Post "%s" not found.</error>', $identifier));
            return Command::FAILURE;
        }

        $tagIds = $this->postTagLink->getTagIdsForPost((int) $post->getId());
        $lines = [
            'post_id' => (string) $post->getId(),
            'title' => (string) $post->getTitle(),
            'author' => (string) $post->getAuthor(),
            'url_key' => (string) $post->getUrlKey(),
            'is_active' => (int) $post->getIsActive() ? 'enabled' : 'disabled',
            'category_id' => (string) $post->getCategoryId(),
            'store_id' => (string) $post->getStoreId(),
            'tag_ids' => $tagIds ? implode(',', $tagIds) : '',
            'published_at' => (string) $post->getPublishedAt(),
            'creation_time' => (string) $post->getCreationTime(),
            'update_time' => (string) $post->getUpdateTime(),
            'excerpt' => (string) $post->getExcerpt(),
            'featured_image' => (string) $post->getFeaturedImage(),
            'meta_title' => (string) $post->getMetaTitle(),
            'meta_description' => (string) $post->getMetaDescription(),
            'meta_robots' => (string) $post->getData('meta_robots'),
        ];

        foreach ($lines as $key => $value) {
            $output->writeln(sprintf('<info>%s</info>: %s', $key, $value));
        }

        return Command::SUCCESS;
    }
}

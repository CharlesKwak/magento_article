<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Api\Data\PostInterfaceFactory;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;

/**
 * bin/magento blogarticle:post:create --title=... --content=...
 */
class PostCreateCommand extends Command
{
    private $postRepository;
    private $postFactory;

    public function __construct(
        PostRepositoryInterface $postRepository,
        PostInterfaceFactory $postFactory,
        ?string $name = null
    ) {
        $this->postRepository = $postRepository;
        $this->postFactory = $postFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:post:create')
            ->setDescription('Create a blog post')
            ->addOption('title', null, InputOption::VALUE_REQUIRED, 'Post title')
            ->addOption('content', null, InputOption::VALUE_REQUIRED, 'Post HTML/text content')
            ->addOption('url-key', null, InputOption::VALUE_REQUIRED, 'URL key (auto from title if omitted)')
            ->addOption('author', null, InputOption::VALUE_REQUIRED, 'Author display name')
            ->addOption('excerpt', null, InputOption::VALUE_REQUIRED, 'Excerpt')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'enabled or disabled', 'enabled')
            ->addOption('category-id', null, InputOption::VALUE_REQUIRED, 'Category ID')
            ->addOption('store-id', null, InputOption::VALUE_REQUIRED, 'Store ID (0/omit = all stores)')
            ->addOption('tag-ids', null, InputOption::VALUE_REQUIRED, 'Comma-separated tag IDs')
            ->addOption('published-at', null, InputOption::VALUE_REQUIRED, 'Published at YYYY-MM-DD HH:MM:SS')
            ->addOption('meta-title', null, InputOption::VALUE_REQUIRED, 'Meta title')
            ->addOption('meta-description', null, InputOption::VALUE_REQUIRED, 'Meta description')
            ->addOption(
                'meta-robots',
                null,
                InputOption::VALUE_REQUIRED,
                'Robots: INDEX,FOLLOW | NOINDEX,FOLLOW | INDEX,NOFOLLOW | NOINDEX,NOFOLLOW'
            )
            ->addOption('featured-image', null, InputOption::VALUE_REQUIRED, 'Featured image URL or media path');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $title = trim((string) $input->getOption('title'));
        $content = trim((string) $input->getOption('content'));
        if ($title === '' || $content === '') {
            $output->writeln('<error>--title and --content are required.</error>');
            return Command::FAILURE;
        }

        $status = strtolower(trim((string) $input->getOption('status')));
        if (!in_array($status, ['enabled', 'disabled', '1', '0'], true)) {
            $output->writeln('<error>--status must be enabled or disabled.</error>');
            return Command::FAILURE;
        }
        $isActive = in_array($status, ['enabled', '1'], true) ? 1 : 0;

        $post = $this->postFactory->create();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setIsActive($isActive);

        $urlKey = trim((string) $input->getOption('url-key'));
        if ($urlKey !== '') {
            $post->setUrlKey($urlKey);
        }
        $author = trim((string) $input->getOption('author'));
        if ($author !== '') {
            $post->setAuthor($author);
        }
        $excerpt = trim((string) $input->getOption('excerpt'));
        if ($excerpt !== '') {
            $post->setExcerpt($excerpt);
        }
        $categoryId = (int) $input->getOption('category-id');
        if ($categoryId > 0) {
            $post->setCategoryId($categoryId);
        }
        $storeId = $input->getOption('store-id');
        if ($storeId !== null && $storeId !== '') {
            $post->setStoreId((int) $storeId);
        }
        $tagIdsRaw = trim((string) $input->getOption('tag-ids'));
        if ($tagIdsRaw !== '') {
            $tagIds = array_values(array_filter(array_map('intval', explode(',', $tagIdsRaw))));
            $post->setTagIds($tagIds);
        }
        $publishedAt = trim((string) $input->getOption('published-at'));
        if ($publishedAt !== '') {
            $post->setPublishedAt($publishedAt);
        }
        $metaTitle = trim((string) $input->getOption('meta-title'));
        if ($metaTitle !== '') {
            $post->setMetaTitle($metaTitle);
        }
        $metaDescription = trim((string) $input->getOption('meta-description'));
        if ($metaDescription !== '') {
            $post->setMetaDescription($metaDescription);
        }
        $metaRobots = strtoupper(trim((string) $input->getOption('meta-robots')));
        if ($metaRobots !== '') {
            $allowed = ['INDEX,FOLLOW', 'NOINDEX,FOLLOW', 'INDEX,NOFOLLOW', 'NOINDEX,NOFOLLOW'];
            if (!in_array($metaRobots, $allowed, true)) {
                $output->writeln('<error>Invalid --meta-robots value.</error>');
                return Command::FAILURE;
            }
            $post->setMetaRobots($metaRobots);
        }
        $featured = trim((string) $input->getOption('featured-image'));
        if ($featured !== '') {
            $post->setFeaturedImage($featured);
        }

        try {
            $saved = $this->postRepository->save($post);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Could not create post: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Created post #%d "%s" (url_key=%s, status=%s).</info>',
            (int) $saved->getPostId(),
            (string) $saved->getTitle(),
            (string) $saved->getUrlKey(),
            (int) $saved->getIsActive() ? 'enabled' : 'disabled'
        ));

        return Command::SUCCESS;
    }
}

<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;

/**
 * bin/magento blogarticle:post:update --id=1 --meta-robots=NOINDEX,FOLLOW
 */
class PostUpdateCommand extends Command
{
    private PostRepositoryInterface $postRepository;

    public function __construct(
        PostRepositoryInterface $postRepository,
        ?string $name = null
    ) {
        $this->postRepository = $postRepository;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:post:update')
            ->setDescription('Update fields on an existing blog post')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Post ID')
            ->addOption('title', null, InputOption::VALUE_REQUIRED, 'Post title')
            ->addOption('content', null, InputOption::VALUE_REQUIRED, 'Post content')
            ->addOption('url-key', null, InputOption::VALUE_REQUIRED, 'URL key')
            ->addOption('author', null, InputOption::VALUE_REQUIRED, 'Author display name')
            ->addOption('excerpt', null, InputOption::VALUE_REQUIRED, 'Excerpt')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'enabled or disabled')
            ->addOption('category-id', null, InputOption::VALUE_REQUIRED, 'Category ID (0 clears)')
            ->addOption('meta-title', null, InputOption::VALUE_REQUIRED, 'Meta title')
            ->addOption('meta-description', null, InputOption::VALUE_REQUIRED, 'Meta description')
            ->addOption(
                'meta-robots',
                null,
                InputOption::VALUE_REQUIRED,
                'INDEX,FOLLOW | NOINDEX,FOLLOW | INDEX,NOFOLLOW | NOINDEX,NOFOLLOW | empty to clear'
            )
            ->addOption('featured-image', null, InputOption::VALUE_REQUIRED, 'Featured image path/URL');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $postId = (int) $input->getOption('id');
        if ($postId <= 0) {
            $output->writeln('<error>--id is required.</error>');
            return Command::FAILURE;
        }

        try {
            $post = $this->postRepository->getById($postId, false);
        } catch (NoSuchEntityException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $changed = false;
        $title = $input->getOption('title');
        if ($title !== null) {
            $post->setTitle(trim((string) $title));
            $changed = true;
        }
        $content = $input->getOption('content');
        if ($content !== null) {
            $post->setContent((string) $content);
            $changed = true;
        }
        $urlKey = $input->getOption('url-key');
        if ($urlKey !== null) {
            $post->setUrlKey(trim((string) $urlKey));
            $changed = true;
        }
        $author = $input->getOption('author');
        if ($author !== null) {
            $post->setAuthor(trim((string) $author) !== '' ? trim((string) $author) : null);
            $changed = true;
        }
        $excerpt = $input->getOption('excerpt');
        if ($excerpt !== null) {
            $post->setExcerpt(trim((string) $excerpt) !== '' ? trim((string) $excerpt) : null);
            $changed = true;
        }
        $status = $input->getOption('status');
        if ($status !== null) {
            $status = strtolower(trim((string) $status));
            if (!in_array($status, ['enabled', 'disabled', '1', '0'], true)) {
                $output->writeln('<error>--status must be enabled or disabled.</error>');
                return Command::FAILURE;
            }
            $post->setIsActive(in_array($status, ['enabled', '1'], true) ? 1 : 0);
            $changed = true;
        }
        $categoryId = $input->getOption('category-id');
        if ($categoryId !== null) {
            $cid = (int) $categoryId;
            $post->setCategoryId($cid > 0 ? $cid : null);
            $changed = true;
        }
        $metaTitle = $input->getOption('meta-title');
        if ($metaTitle !== null) {
            $post->setMetaTitle(trim((string) $metaTitle) !== '' ? trim((string) $metaTitle) : null);
            $changed = true;
        }
        $metaDescription = $input->getOption('meta-description');
        if ($metaDescription !== null) {
            $post->setMetaDescription(
                trim((string) $metaDescription) !== '' ? trim((string) $metaDescription) : null
            );
            $changed = true;
        }
        $metaRobots = $input->getOption('meta-robots');
        if ($metaRobots !== null) {
            $metaRobots = strtoupper(trim((string) $metaRobots));
            if ($metaRobots === '') {
                $post->setMetaRobots(null);
            } else {
                $allowed = ['INDEX,FOLLOW', 'NOINDEX,FOLLOW', 'INDEX,NOFOLLOW', 'NOINDEX,NOFOLLOW'];
                if (!in_array($metaRobots, $allowed, true)) {
                    $output->writeln('<error>Invalid --meta-robots value.</error>');
                    return Command::FAILURE;
                }
                $post->setMetaRobots($metaRobots);
            }
            $changed = true;
        }
        $featured = $input->getOption('featured-image');
        if ($featured !== null) {
            $post->setFeaturedImage(trim((string) $featured) !== '' ? trim((string) $featured) : null);
            $changed = true;
        }

        if (!$changed) {
            $output->writeln('<comment>No fields to update. Pass at least one option besides --id.</comment>');
            return Command::FAILURE;
        }

        try {
            $saved = $this->postRepository->save($post);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Could not update post: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Updated post #%d "%s" (robots=%s).</info>',
            (int) $saved->getPostId(),
            (string) $saved->getTitle(),
            $saved->getMetaRobots() ?: 'INDEX,FOLLOW'
        ));
        return Command::SUCCESS;
    }
}

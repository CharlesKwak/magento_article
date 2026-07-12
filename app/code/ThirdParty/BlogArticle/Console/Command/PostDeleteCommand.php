<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\PostFactory;

/**
 * bin/magento blogarticle:post:delete <id|url_key> --force
 */
class PostDeleteCommand extends Command
{
    private $postRepository;
    private $postFactory;

    public function __construct(
        PostRepositoryInterface $postRepository,
        PostFactory $postFactory,
        ?string $name = null
    ) {
        $this->postRepository = $postRepository;
        $this->postFactory = $postFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:post:delete')
            ->setDescription('Delete a blog post by ID or URL key')
            ->addArgument('identifier', InputArgument::REQUIRED, 'Post ID or url_key')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Required confirmation flag to actually delete'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('force')) {
            $output->writeln('<error>Refusing to delete without --force.</error>');
            $output->writeln('Example: bin/magento blogarticle:post:delete 12 --force');
            return Command::FAILURE;
        }

        $identifier = trim((string) $input->getArgument('identifier'));
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

        $postId = (int) $post->getId();
        $title = (string) $post->getTitle();

        try {
            $this->postRepository->deleteById($postId);
        } catch (NoSuchEntityException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Could not delete post: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Deleted post #%d "%s".</info>', $postId, $title));
        return Command::SUCCESS;
    }
}

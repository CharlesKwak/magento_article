<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\PostFactory;

/**
 * bin/magento blogarticle:post:set-status <id> enabled|disabled
 */
class PostSetStatusCommand extends Command
{
    private const ARG_POST_ID = 'post_id';
    private const ARG_STATUS = 'status';

    private $postFactory;

    public function __construct(PostFactory $postFactory, ?string $name = null)
    {
        $this->postFactory = $postFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:post:set-status')
            ->setDescription('Enable or disable a blog post by ID')
            ->addArgument(
                self::ARG_POST_ID,
                InputArgument::REQUIRED,
                'Post ID'
            )
            ->addArgument(
                self::ARG_STATUS,
                InputArgument::REQUIRED,
                'Status: enabled or disabled'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $postId = (int) $input->getArgument(self::ARG_POST_ID);
        $status = strtolower(trim((string) $input->getArgument(self::ARG_STATUS)));

        if ($postId <= 0) {
            $output->writeln('<error>post_id must be a positive integer.</error>');
            return Command::FAILURE;
        }
        if (!in_array($status, ['enabled', 'disabled', '1', '0', 'enable', 'disable'], true)) {
            $output->writeln('<error>Status must be enabled or disabled.</error>');
            return Command::FAILURE;
        }

        $isActive = in_array($status, ['enabled', '1', 'enable'], true) ? 1 : 0;

        $post = $this->postFactory->create()->load($postId);
        if (!$post->getId()) {
            $output->writeln(sprintf('<error>Post with ID %d does not exist.</error>', $postId));
            return Command::FAILURE;
        }

        try {
            $post->setIsActive($isActive);
            $post->save();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Post #%d "%s" is now %s.</info>',
            (int) $post->getId(),
            (string) $post->getTitle(),
            $isActive ? 'enabled' : 'disabled'
        ));

        return Command::SUCCESS;
    }
}

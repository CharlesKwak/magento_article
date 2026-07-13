<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\TagFactory;

/**
 * bin/magento blogarticle:tag:set-status <id> enabled|disabled
 */
class TagSetStatusCommand extends Command
{
    private $tagFactory;

    public function __construct(TagFactory $tagFactory, ?string $name = null)
    {
        $this->tagFactory = $tagFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:tag:set-status')
            ->setDescription('Enable or disable a blog tag by ID')
            ->addArgument('tag_id', InputArgument::REQUIRED, 'Tag ID')
            ->addArgument('status', InputArgument::REQUIRED, 'enabled or disabled');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (int) $input->getArgument('tag_id');
        $status = strtolower(trim((string) $input->getArgument('status')));
        if ($id <= 0) {
            $output->writeln('<error>tag_id must be a positive integer.</error>');
            return Command::FAILURE;
        }
        if (!in_array($status, ['enabled', 'disabled', '1', '0', 'enable', 'disable'], true)) {
            $output->writeln('<error>Status must be enabled or disabled.</error>');
            return Command::FAILURE;
        }

        $tag = $this->tagFactory->create()->load($id);
        if (!$tag->getId()) {
            $output->writeln(sprintf('<error>Tag with ID %d does not exist.</error>', $id));
            return Command::FAILURE;
        }

        $isActive = in_array($status, ['enabled', '1', 'enable'], true) ? 1 : 0;
        try {
            $tag->setIsActive($isActive);
            $tag->save();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Tag #%d "%s" is now %s.</info>',
            (int) $tag->getId(),
            (string) $tag->getName(),
            $isActive ? 'enabled' : 'disabled'
        ));
        return Command::SUCCESS;
    }
}

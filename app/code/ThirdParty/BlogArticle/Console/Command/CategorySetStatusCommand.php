<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\CategoryFactory;

/**
 * bin/magento blogarticle:category:set-status <id> enabled|disabled
 */
class CategorySetStatusCommand extends Command
{
    private $categoryFactory;

    public function __construct(CategoryFactory $categoryFactory, ?string $name = null)
    {
        $this->categoryFactory = $categoryFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:category:set-status')
            ->setDescription('Enable or disable a blog category by ID')
            ->addArgument('category_id', InputArgument::REQUIRED, 'Category ID')
            ->addArgument('status', InputArgument::REQUIRED, 'enabled or disabled');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = (int) $input->getArgument('category_id');
        $status = strtolower(trim((string) $input->getArgument('status')));
        if ($id <= 0) {
            $output->writeln('<error>category_id must be a positive integer.</error>');
            return Command::FAILURE;
        }
        if (!in_array($status, ['enabled', 'disabled', '1', '0', 'enable', 'disable'], true)) {
            $output->writeln('<error>Status must be enabled or disabled.</error>');
            return Command::FAILURE;
        }

        $category = $this->categoryFactory->create()->load($id);
        if (!$category->getId()) {
            $output->writeln(sprintf('<error>Category with ID %d does not exist.</error>', $id));
            return Command::FAILURE;
        }

        $isActive = in_array($status, ['enabled', '1', 'enable'], true) ? 1 : 0;
        try {
            $category->setIsActive($isActive);
            $category->save();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Category #%d "%s" is now %s.</info>',
            (int) $category->getId(),
            (string) $category->getName(),
            $isActive ? 'enabled' : 'disabled'
        ));
        return Command::SUCCESS;
    }
}

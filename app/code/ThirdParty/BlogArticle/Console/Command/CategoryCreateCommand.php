<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Api\CategoryRepositoryInterface;
use ThirdParty\BlogArticle\Api\Data\CategoryInterfaceFactory;

/**
 * bin/magento blogarticle:category:create --name=...
 */
class CategoryCreateCommand extends Command
{
    private $categoryRepository;
    private $categoryFactory;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryInterfaceFactory $categoryFactory,
        ?string $name = null
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:category:create')
            ->setDescription('Create a blog category')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Category name')
            ->addOption('url-key', null, InputOption::VALUE_REQUIRED, 'URL key (auto from name if omitted)')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'enabled or disabled', 'enabled')
            ->addOption('sort-order', null, InputOption::VALUE_REQUIRED, 'Sort order (lower first)', '0');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = trim((string) $input->getOption('name'));
        if ($name === '') {
            $output->writeln('<error>--name is required.</error>');
            return Command::FAILURE;
        }
        $status = strtolower(trim((string) $input->getOption('status')));
        if (!in_array($status, ['enabled', 'disabled', '1', '0'], true)) {
            $output->writeln('<error>--status must be enabled or disabled.</error>');
            return Command::FAILURE;
        }

        $category = $this->categoryFactory->create();
        $category->setName($name);
        $category->setIsActive(in_array($status, ['enabled', '1'], true) ? 1 : 0);
        $urlKey = trim((string) $input->getOption('url-key'));
        if ($urlKey !== '') {
            $category->setUrlKey($urlKey);
        }
        $sortOrder = max(0, (int) $input->getOption('sort-order'));
        $category->setSortOrder($sortOrder);

        try {
            $saved = $this->categoryRepository->save($category);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Could not create category: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Created category #%d "%s" (url_key=%s, sort_order=%d).</info>',
            (int) $saved->getCategoryId(),
            (string) $saved->getName(),
            (string) $saved->getUrlKey(),
            (int) $saved->getSortOrder()
        ));
        return Command::SUCCESS;
    }
}

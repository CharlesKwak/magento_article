<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Api\CategoryRepositoryInterface;
use ThirdParty\BlogArticle\Model\CategoryFactory;

/**
 * bin/magento blogarticle:category:delete <id|url_key> --force
 */
class CategoryDeleteCommand extends Command
{
    private $categoryRepository;
    private $categoryFactory;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryFactory $categoryFactory,
        ?string $name = null
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:category:delete')
            ->setDescription('Delete a blog category by ID or URL key')
            ->addArgument('identifier', InputArgument::REQUIRED, 'Category ID or url_key')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Required confirmation flag');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('force')) {
            $output->writeln('<error>Refusing to delete without --force.</error>');
            $output->writeln('Example: bin/magento blogarticle:category:delete 3 --force');
            return Command::FAILURE;
        }

        $identifier = trim((string) $input->getArgument('identifier'));
        if ($identifier === '') {
            $output->writeln('<error>Identifier is required.</error>');
            return Command::FAILURE;
        }

        $category = $this->categoryFactory->create();
        if (ctype_digit($identifier)) {
            $category->load((int) $identifier);
        } else {
            $category->load($identifier, 'url_key');
        }
        if (!$category->getId()) {
            $output->writeln(sprintf('<error>Category "%s" not found.</error>', $identifier));
            return Command::FAILURE;
        }

        $id = (int) $category->getId();
        $name = (string) $category->getName();

        try {
            $this->categoryRepository->deleteById($id);
        } catch (NoSuchEntityException | LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Could not delete category: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Deleted category #%d "%s".</info>', $id, $name));
        return Command::SUCCESS;
    }
}

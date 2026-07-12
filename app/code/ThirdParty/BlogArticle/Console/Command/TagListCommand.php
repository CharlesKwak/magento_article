<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory;

/**
 * bin/magento blogarticle:tag:list
 */
class TagListCommand extends Command
{
    private $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory, ?string $name = null)
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:tag:list')
            ->setDescription('List blog tags')
            ->addOption(
                'status',
                null,
                InputOption::VALUE_REQUIRED,
                'enabled, disabled, or all',
                'all'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $status = strtolower(trim((string) $input->getOption('status')));
        if (!in_array($status, ['all', 'enabled', 'disabled'], true)) {
            $output->writeln('<error>Invalid --status. Use enabled, disabled, or all.</error>');
            return Command::FAILURE;
        }

        $collection = $this->collectionFactory->create();
        if ($status === 'enabled') {
            $collection->addFieldToFilter('is_active', 1);
        } elseif ($status === 'disabled') {
            $collection->addFieldToFilter('is_active', 0);
        }
        $collection->setOrder('name', 'ASC');

        $rows = [];
        foreach ($collection as $item) {
            $rows[] = [
                (string) $item->getId(),
                (string) $item->getName(),
                (string) $item->getUrlKey(),
                (int) $item->getIsActive() ? 'enabled' : 'disabled',
            ];
        }
        if (!$rows) {
            $output->writeln('<info>No tags found.</info>');
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'URL Key', 'Status']);
        $table->setRows($rows);
        $table->render();
        return Command::SUCCESS;
    }
}

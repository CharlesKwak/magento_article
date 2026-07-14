<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

/**
 * bin/magento blogarticle:post:list
 */
class PostListCommand extends Command
{
    private const OPTION_STATUS = 'status';
    private const OPTION_LIMIT = 'limit';
    private const OPTION_SEARCH = 'search';

    private CollectionFactory $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory, ?string $name = null)
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:post:list')
            ->setDescription('List blog posts (id, title, url_key, author, status)')
            ->addOption(
                self::OPTION_STATUS,
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by status: enabled, disabled, or all (default: all)'
            )
            ->addOption(
                self::OPTION_LIMIT,
                'l',
                InputOption::VALUE_REQUIRED,
                'Maximum rows to display',
                '50'
            )
            ->addOption(
                self::OPTION_SEARCH,
                's',
                InputOption::VALUE_REQUIRED,
                'Search title, url_key, or content'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $status = strtolower(trim((string) $input->getOption(self::OPTION_STATUS)));
        if ($status === '') {
            $status = 'all';
        }
        if (!in_array($status, ['all', 'enabled', 'disabled'], true)) {
            $output->writeln('<error>Invalid --status. Use enabled, disabled, or all.</error>');
            return Command::FAILURE;
        }

        $limit = max(1, min(500, (int) $input->getOption(self::OPTION_LIMIT)));
        $search = trim((string) $input->getOption(self::OPTION_SEARCH));

        $collection = $this->collectionFactory->create();
        if ($status === 'enabled') {
            $collection->addFieldToFilter('is_active', 1);
        } elseif ($status === 'disabled') {
            $collection->addFieldToFilter('is_active', 0);
        }
        if ($search !== '') {
            $collection->addFieldToFilter(
                ['title', 'url_key', 'content', 'author'],
                [
                    ['like' => '%' . $search . '%'],
                    ['like' => '%' . $search . '%'],
                    ['like' => '%' . $search . '%'],
                    ['like' => '%' . $search . '%'],
                ]
            );
        }
        $collection->setOrder('post_id', 'ASC');
        $collection->setPageSize($limit);
        $collection->setCurPage(1);

        $rows = [];
        foreach ($collection as $post) {
            $rows[] = [
                (string) $post->getId(),
                (string) $post->getTitle(),
                (string) $post->getUrlKey(),
                (string) $post->getAuthor(),
                (int) $post->getIsActive() ? 'enabled' : 'disabled',
            ];
        }

        if (!$rows) {
            $output->writeln('<info>No posts found.</info>');
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['ID', 'Title', 'URL Key', 'Author', 'Status']);
        $table->setRows($rows);
        $table->render();
        $output->writeln(sprintf('<comment>Showing %d row(s) (limit %d).</comment>', count($rows), $limit));

        return Command::SUCCESS;
    }
}

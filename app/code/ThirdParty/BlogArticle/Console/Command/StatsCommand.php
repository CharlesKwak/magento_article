<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Api\BlogStatsManagementInterface;

/**
 * bin/magento blogarticle:stats
 */
class StatsCommand extends Command
{
    private BlogStatsManagementInterface $statsManagement;

    public function __construct(
        BlogStatsManagementInterface $statsManagement,
        ?string $name = null
    ) {
        $this->statsManagement = $statsManagement;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:stats')
            ->setDescription('Show blog aggregate statistics (posts, comments, views)');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stats = $this->statsManagement->get();
        $rows = [
            'posts_total' => $stats->getPostsTotal(),
            'posts_enabled' => $stats->getPostsEnabled(),
            'comments_total' => $stats->getCommentsTotal(),
            'comments_pending' => $stats->getCommentsPending(),
            'categories_total' => $stats->getCategoriesTotal(),
            'tags_total' => $stats->getTagsTotal(),
            'views_total' => $stats->getViewsTotal(),
        ];
        foreach ($rows as $key => $value) {
            $output->writeln(sprintf('<info>%s</info>: %s', $key, $value));
        }
        return Command::SUCCESS;
    }
}

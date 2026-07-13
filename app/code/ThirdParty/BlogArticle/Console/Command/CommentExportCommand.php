<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\CommentCsvExporter;

/**
 * bin/magento blogarticle:comment:export
 */
class CommentExportCommand extends Command
{
    private $exporter;

    public function __construct(CommentCsvExporter $exporter, ?string $name = null)
    {
        $this->exporter = $exporter;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:comment:export')
            ->setDescription('Export blog comments to CSV')
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Output CSV path (default: var/export/...)')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'all|approved|pending', 'all')
            ->addOption('post-id', null, InputOption::VALUE_REQUIRED, 'Filter by post ID');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getOption('file');
        $file = $file !== null && $file !== '' ? (string) $file : null;
        $status = (string) $input->getOption('status');
        $postIdOpt = $input->getOption('post-id');
        $postId = $postIdOpt !== null && $postIdOpt !== '' ? (int) $postIdOpt : null;

        try {
            $result = $this->exporter->export($file, $status, $postId);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Export failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Exported %d comment(s) to %s</info>',
            $result['count'],
            $result['path']
        ));
        return Command::SUCCESS;
    }
}

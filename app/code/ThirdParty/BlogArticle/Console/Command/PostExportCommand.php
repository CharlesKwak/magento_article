<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\PostCsvExporter;

/**
 * bin/magento blogarticle:post:export
 */
class PostExportCommand extends Command
{
    private PostCsvExporter $exporter;

    public function __construct(PostCsvExporter $exporter, ?string $name = null)
    {
        $this->exporter = $exporter;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:post:export')
            ->setDescription('Export blog posts to CSV (import-compatible columns)')
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Output CSV path (default: var/export/...)')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'all|enabled|disabled', 'all');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getOption('file');
        $file = $file !== null && $file !== '' ? (string) $file : null;
        $status = (string) $input->getOption('status');

        try {
            $result = $this->exporter->export($file, $status);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Export failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Exported %d post(s) to %s</info>',
            $result['count'],
            $result['path']
        ));
        return Command::SUCCESS;
    }
}

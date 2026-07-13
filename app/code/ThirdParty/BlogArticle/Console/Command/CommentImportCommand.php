<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\CommentCsvImporter;

/**
 * bin/magento blogarticle:comment:import path/to/comments.csv
 */
class CommentImportCommand extends Command
{
    private $importer;

    public function __construct(CommentCsvImporter $importer, ?string $name = null)
    {
        $this->importer = $importer;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:comment:import')
            ->setDescription('Import blog comments from a CSV file')
            ->addArgument('file', InputArgument::REQUIRED, 'Absolute or relative path to CSV')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate only; do not write')
            ->addOption(
                'update',
                'u',
                InputOption::VALUE_NONE,
                'Update rows matched by comment_id instead of failing'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = trim((string) $input->getArgument('file'));
        if ($file === '') {
            $output->writeln('<error>File path is required.</error>');
            return Command::FAILURE;
        }
        if ($file[0] !== '/' && !(strlen($file) > 2 && $file[1] === ':')) {
            $file = getcwd() . DIRECTORY_SEPARATOR . $file;
        }

        $dryRun = (bool) $input->getOption('dry-run');
        $update = (bool) $input->getOption('update');
        if ($dryRun) {
            $output->writeln('<comment>Dry-run mode: no changes will be saved.</comment>');
        }

        try {
            $result = $this->importer->import($file, $dryRun, $update);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Import failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Comment import complete: created=%d, updated=%d, skipped=%d.</info>',
            $result['created'],
            $result['updated'],
            $result['skipped']
        ));
        foreach ($result['errors'] as $error) {
            $output->writeln('<error>' . $error . '</error>');
        }

        return $result['errors'] ? Command::FAILURE : Command::SUCCESS;
    }
}

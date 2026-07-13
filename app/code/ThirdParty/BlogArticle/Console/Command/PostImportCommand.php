<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\PostCsvImporter;

/**
 * bin/magento blogarticle:post:import path/to/posts.csv
 */
class PostImportCommand extends Command
{
    private $importer;

    public function __construct(PostCsvImporter $importer, ?string $name = null)
    {
        $this->importer = $importer;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:post:import')
            ->setDescription('Import blog posts from a CSV file (native or WordPress column names)')
            ->addArgument('file', InputArgument::REQUIRED, 'Absolute or relative path to CSV')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate only; do not write')
            ->addOption(
                'update',
                'u',
                InputOption::VALUE_NONE,
                'Update existing posts matched by url_key instead of failing'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'CSV column format: native|wordpress',
                'native'
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
            // relative to CWD
            $file = getcwd() . DIRECTORY_SEPARATOR . $file;
        }

        $dryRun = (bool) $input->getOption('dry-run');
        $update = (bool) $input->getOption('update');
        $format = (string) $input->getOption('format');

        if ($dryRun) {
            $output->writeln('<comment>Dry-run mode: no changes will be saved.</comment>');
        }
        $output->writeln(sprintf('<comment>Import format: %s</comment>', $format));

        try {
            $result = $this->importer->import($file, $dryRun, $update, $format);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Import failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Import complete: created=%d, updated=%d, skipped=%d.</info>',
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

<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory;
use ThirdParty\BlogArticle\Model\TaxonomyCsvExporter;

/**
 * bin/magento blogarticle:tag:export
 */
class TagExportCommand extends Command
{
    private $exporter;
    private $collectionFactory;

    public function __construct(
        TaxonomyCsvExporter $exporter,
        CollectionFactory $collectionFactory,
        ?string $name = null
    ) {
        $this->exporter = $exporter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:tag:export')
            ->setDescription('Export blog tags to CSV')
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Output CSV path')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'all|enabled|disabled', 'all');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getOption('file');
        $file = $file !== null && $file !== '' ? (string) $file : null;
        try {
            $result = $this->exporter->export(
                $this->collectionFactory->create(),
                'tag_id',
                'blogarticle_tags',
                $file,
                (string) $input->getOption('status')
            );
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Export failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Exported %d tag(s) to %s</info>',
            $result['count'],
            $result['path']
        ));
        return Command::SUCCESS;
    }
}

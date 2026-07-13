<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Model\PostDuplicator;

/**
 * bin/magento blogarticle:post:duplicate --id=12
 */
class PostDuplicateCommand extends Command
{
    private $postDuplicator;

    public function __construct(
        PostDuplicator $postDuplicator,
        ?string $name = null
    ) {
        $this->postDuplicator = $postDuplicator;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:post:duplicate')
            ->setDescription('Duplicate a blog post as a disabled draft (copies tags and product links)')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Source post ID')
            ->addOption(
                'keep-status',
                null,
                InputOption::VALUE_NONE,
                'Keep source is_active instead of forcing disabled'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (int) $input->getOption('id');
        if ($id <= 0) {
            $output->writeln('<error>--id is required</error>');
            return Command::FAILURE;
        }
        try {
            $copy = $this->postDuplicator->duplicate($id, (bool) $input->getOption('keep-status'));
            $output->writeln(sprintf(
                '<info>Duplicated post %d → new post_id=%d url_key=%s is_active=%d</info>',
                $id,
                (int) $copy->getId(),
                (string) $copy->getUrlKey(),
                (int) $copy->getIsActive()
            ));
            return Command::SUCCESS;
        } catch (NoSuchEntityException | LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}

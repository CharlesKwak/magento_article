<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Api\TagRepositoryInterface;
use ThirdParty\BlogArticle\Model\TagFactory;

/**
 * bin/magento blogarticle:tag:delete <id|url_key> --force
 */
class TagDeleteCommand extends Command
{
    private TagRepositoryInterface $tagRepository;
    private TagFactory $tagFactory;

    public function __construct(
        TagRepositoryInterface $tagRepository,
        TagFactory $tagFactory,
        ?string $name = null
    ) {
        $this->tagRepository = $tagRepository;
        $this->tagFactory = $tagFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:tag:delete')
            ->setDescription('Delete a blog tag by ID or URL key')
            ->addArgument('identifier', InputArgument::REQUIRED, 'Tag ID or url_key')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Required confirmation flag');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('force')) {
            $output->writeln('<error>Refusing to delete without --force.</error>');
            $output->writeln('Example: bin/magento blogarticle:tag:delete 3 --force');
            return Command::FAILURE;
        }

        $identifier = trim((string) $input->getArgument('identifier'));
        if ($identifier === '') {
            $output->writeln('<error>Identifier is required.</error>');
            return Command::FAILURE;
        }

        $tag = $this->tagFactory->create();
        if (ctype_digit($identifier)) {
            $tag->load((int) $identifier);
        } else {
            $tag->load($identifier, 'url_key');
        }
        if (!$tag->getId()) {
            $output->writeln(sprintf('<error>Tag "%s" not found.</error>', $identifier));
            return Command::FAILURE;
        }

        $id = (int) $tag->getId();
        $name = (string) $tag->getName();

        try {
            $this->tagRepository->deleteById($id);
        } catch (NoSuchEntityException | LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Could not delete tag: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Deleted tag #%d "%s".</info>', $id, $name));
        return Command::SUCCESS;
    }
}

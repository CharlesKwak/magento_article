<?php
namespace ThirdParty\BlogArticle\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThirdParty\BlogArticle\Api\Data\TagInterfaceFactory;
use ThirdParty\BlogArticle\Api\TagRepositoryInterface;

/**
 * bin/magento blogarticle:tag:create --name=...
 */
class TagCreateCommand extends Command
{
    private $tagRepository;
    private $tagFactory;

    public function __construct(
        TagRepositoryInterface $tagRepository,
        TagInterfaceFactory $tagFactory,
        ?string $name = null
    ) {
        $this->tagRepository = $tagRepository;
        $this->tagFactory = $tagFactory;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('blogarticle:tag:create')
            ->setDescription('Create a blog tag')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Tag name')
            ->addOption('url-key', null, InputOption::VALUE_REQUIRED, 'URL key (auto from name if omitted)')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'enabled or disabled', 'enabled');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = trim((string) $input->getOption('name'));
        if ($name === '') {
            $output->writeln('<error>--name is required.</error>');
            return Command::FAILURE;
        }
        $status = strtolower(trim((string) $input->getOption('status')));
        if (!in_array($status, ['enabled', 'disabled', '1', '0'], true)) {
            $output->writeln('<error>--status must be enabled or disabled.</error>');
            return Command::FAILURE;
        }

        $tag = $this->tagFactory->create();
        $tag->setName($name);
        $tag->setIsActive(in_array($status, ['enabled', '1'], true) ? 1 : 0);
        $urlKey = trim((string) $input->getOption('url-key'));
        if ($urlKey !== '') {
            $tag->setUrlKey($urlKey);
        }

        try {
            $saved = $this->tagRepository->save($tag);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>Could not create tag: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Created tag #%d "%s" (url_key=%s).</info>',
            (int) $saved->getTagId(),
            (string) $saved->getName(),
            (string) $saved->getUrlKey()
        ));
        return Command::SUCCESS;
    }
}

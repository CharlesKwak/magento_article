<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

/**
 * Exports posts to CSV (import-compatible columns).
 */
class PostCsvExporter
{
    public const HEADERS = [
        'post_id',
        'title',
        'content',
        'url_key',
        'author',
        'status',
        'excerpt',
        'category_id',
        'tag_ids',
        'store_id',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_robots',
        'featured_image',
        'creation_time',
        'update_time',
    ];

    private Csv $csv;
    private Filesystem $filesystem;
    private CollectionFactory $collectionFactory;
    private PostTagLink $postTagLink;
    private PostFilter $postFilter;

    public function __construct(
        Csv $csv,
        Filesystem $filesystem,
        CollectionFactory $collectionFactory,
        PostTagLink $postTagLink,
        PostFilter $postFilter
    ) {
        $this->csv = $csv;
        $this->filesystem = $filesystem;
        $this->collectionFactory = $collectionFactory;
        $this->postTagLink = $postTagLink;
        $this->postFilter = $postFilter;
    }

    /**
     * @param string|null $absolutePath Write path; default var/export/blogarticle_posts_*.csv
     * @param string $status all|enabled|disabled
     * @return array{path:string,count:int}
     * @throws LocalizedException
     */
    public function export(?string $absolutePath = null, string $status = 'all'): array
    {
        $status = strtolower(trim($status));
        if (!in_array($status, ['all', 'enabled', 'disabled'], true)) {
            throw new LocalizedException(__('Invalid status filter.'));
        }

        $collection = $this->collectionFactory->create();
        if ($status === 'enabled') {
            $collection->addFieldToFilter('is_active', 1);
        } elseif ($status === 'disabled') {
            $collection->addFieldToFilter('is_active', 0);
        }
        $this->postFilter->applyDefaultSort($collection);

        $rows = [self::HEADERS];
        foreach ($collection as $post) {
            $tagIds = $this->postTagLink->getTagIdsForPost((int) $post->getId());
            $rows[] = [
                (string) $post->getId(),
                (string) $post->getTitle(),
                (string) $post->getContent(),
                (string) $post->getUrlKey(),
                (string) $post->getAuthor(),
                (int) $post->getIsActive() ? 'enabled' : 'disabled',
                (string) $post->getExcerpt(),
                $post->getCategoryId() !== null && $post->getCategoryId() !== ''
                    ? (string) (int) $post->getCategoryId() : '',
                $tagIds ? implode(',', $tagIds) : '',
                $post->getStoreId() !== null && $post->getStoreId() !== ''
                    ? (string) (int) $post->getStoreId() : '',
                (string) $post->getPublishedAt(),
                (string) $post->getMetaTitle(),
                (string) $post->getMetaDescription(),
                (string) $post->getData('meta_robots'),
                (string) $post->getFeaturedImage(),
                (string) $post->getCreationTime(),
                (string) $post->getUpdateTime(),
            ];
        }

        $path = $this->resolvePath($absolutePath, 'blogarticle_posts');
        $this->writeCsv($path, $rows);

        return ['path' => $path, 'count' => count($rows) - 1];
    }

    private function resolvePath(?string $absolutePath, string $prefix): string
    {
        if ($absolutePath !== null && trim($absolutePath) !== '') {
            $path = trim($absolutePath);
            $dir = dirname($path);
            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new LocalizedException(__('Cannot create export directory: %1', $dir));
            }
            return $path;
        }
        $var = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $relative = 'export/' . $prefix . '_' . date('Ymd_His') . '.csv';
        $var->create('export');
        return $var->getAbsolutePath($relative);
    }

    private function writeCsv(string $path, array $rows): void
    {
        $this->csv->setDelimiter(',');
        $this->csv->setEnclosure('"');
        $this->csv->appendData($path, $rows);
    }
}

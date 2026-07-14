<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use ThirdParty\BlogArticle\Model\ResourceModel\Comment\CollectionFactory;

/**
 * Exports comments to CSV.
 */
class CommentCsvExporter
{
    public const HEADERS = [
        'comment_id',
        'post_id',
        'parent_id',
        'author_name',
        'author_email',
        'content',
        'is_approved',
        'creation_time',
    ];

    private Csv $csv;
    private Filesystem $filesystem;
    private CollectionFactory $collectionFactory;

    public function __construct(
        Csv $csv,
        Filesystem $filesystem,
        CollectionFactory $collectionFactory
    ) {
        $this->csv = $csv;
        $this->filesystem = $filesystem;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param string|null $absolutePath
     * @param string $status all|approved|pending
     * @param int|null $postId
     * @return array{path:string,count:int}
     * @throws LocalizedException
     */
    public function export(?string $absolutePath = null, string $status = 'all', ?int $postId = null): array
    {
        $status = strtolower(trim($status));
        if (!in_array($status, ['all', 'approved', 'pending'], true)) {
            throw new LocalizedException(__('Invalid status filter (all|approved|pending).'));
        }

        $collection = $this->collectionFactory->create();
        if ($status === 'approved') {
            $collection->addFieldToFilter('is_approved', 1);
        } elseif ($status === 'pending') {
            $collection->addFieldToFilter('is_approved', 0);
        }
        if ($postId !== null && $postId > 0) {
            $collection->addFieldToFilter('post_id', $postId);
        }
        $collection->setOrder('comment_id', 'ASC');

        $rows = [self::HEADERS];
        foreach ($collection as $comment) {
            $rows[] = [
                (string) $comment->getId(),
                (string) $comment->getPostId(),
                $comment->getParentId() ? (string) (int) $comment->getParentId() : '',
                (string) $comment->getAuthorName(),
                (string) $comment->getAuthorEmail(),
                (string) $comment->getContent(),
                (int) $comment->getIsApproved() ? '1' : '0',
                (string) $comment->getCreationTime(),
            ];
        }

        $path = $this->resolvePath($absolutePath, 'blogarticle_comments');
        $this->csv->setDelimiter(',');
        $this->csv->setEnclosure('"');
        $this->csv->appendData($path, $rows);

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
}

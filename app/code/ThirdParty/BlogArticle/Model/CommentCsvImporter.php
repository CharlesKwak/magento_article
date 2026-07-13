<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Api\Data\CommentInterfaceFactory;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;

/**
 * Imports comments from CSV (compatible with CommentCsvExporter headers).
 *
 * Required: post_id (or post_url_key), author_name, content
 * Optional: comment_id (with --update), parent_id, author_email, is_approved, creation_time
 */
class CommentCsvImporter
{
    private $csv;
    private $commentRepository;
    private $commentFactory;
    private $commentModelFactory;
    private $postRepository;
    private $postModelFactory;

    public function __construct(
        Csv $csv,
        CommentRepositoryInterface $commentRepository,
        CommentInterfaceFactory $commentFactory,
        CommentFactory $commentModelFactory,
        PostRepositoryInterface $postRepository,
        PostFactory $postModelFactory
    ) {
        $this->csv = $csv;
        $this->commentRepository = $commentRepository;
        $this->commentFactory = $commentFactory;
        $this->commentModelFactory = $commentModelFactory;
        $this->postRepository = $postRepository;
        $this->postModelFactory = $postModelFactory;
    }

    /**
     * @return array{created:int,updated:int,skipped:int,errors:string[]}
     * @throws LocalizedException
     */
    public function import(string $filePath, bool $dryRun = false, bool $update = false): array
    {
        if (!is_readable($filePath)) {
            throw new LocalizedException(__('CSV file is not readable: %1', $filePath));
        }

        $this->csv->setDelimiter(',');
        $this->csv->setEnclosure('"');
        $rows = $this->csv->getData($filePath);
        if (!$rows || count($rows) < 2) {
            throw new LocalizedException(__('CSV must include a header row and at least one data row.'));
        }

        $header = array_map(static function ($h) {
            return strtolower(trim((string) $h));
        }, $rows[0]);

        $hasPostId = in_array('post_id', $header, true);
        $hasPostKey = in_array('post_url_key', $header, true);
        if (!$hasPostId && !$hasPostKey) {
            throw new LocalizedException(__('CSV requires post_id or post_url_key column.'));
        }
        foreach (['author_name', 'content'] as $required) {
            if (!in_array($required, $header, true)) {
                throw new LocalizedException(__('CSV is missing required column: %1', $required));
            }
        }

        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        for ($i = 1, $n = count($rows); $i < $n; $i++) {
            $line = $i + 1;
            $raw = $rows[$i];
            if ($this->isEmptyRow($raw)) {
                continue;
            }
            $data = [];
            foreach ($header as $colIndex => $colName) {
                if ($colName === '') {
                    continue;
                }
                $data[$colName] = isset($raw[$colIndex]) ? trim((string) $raw[$colIndex]) : '';
            }
            try {
                $this->importRow($data, $dryRun, $update, $result);
            } catch (\Exception $e) {
                $result['errors'][] = sprintf('Line %d: %s', $line, $e->getMessage());
                $result['skipped']++;
            }
        }

        return $result;
    }

    /**
     * @param array<string, string> $data
     * @param array{created:int,updated:int,skipped:int,errors:string[]} $result
     */
    private function importRow(array $data, bool $dryRun, bool $update, array &$result): void
    {
        $author = $data['author_name'] ?? '';
        $content = $data['content'] ?? '';
        if ($author === '' || $content === '') {
            throw new LocalizedException(__('author_name and content are required.'));
        }

        $postId = 0;
        if (!empty($data['post_id'])) {
            $postId = (int) $data['post_id'];
        } elseif (!empty($data['post_url_key'])) {
            $post = $this->postModelFactory->create()->load($data['post_url_key'], 'url_key');
            if (!$post->getId()) {
                throw new LocalizedException(__('Unknown post_url_key "%1".', $data['post_url_key']));
            }
            $postId = (int) $post->getId();
        }
        if ($postId <= 0) {
            throw new LocalizedException(__('post_id is required.'));
        }
        // Ensure post exists (admin import may target disabled posts)
        $this->postRepository->getById($postId, false);

        $commentId = !empty($data['comment_id']) ? (int) $data['comment_id'] : 0;
        $existingId = null;
        if ($commentId > 0) {
            $existing = $this->commentModelFactory->create()->load($commentId);
            if ($existing->getId()) {
                if (!$update) {
                    throw new LocalizedException(
                        __('comment_id %1 exists (use --update to overwrite).', $commentId)
                    );
                }
                $existingId = $commentId;
            }
        }

        $approved = 0;
        if (array_key_exists('is_approved', $data) && $data['is_approved'] !== '') {
            $v = strtolower($data['is_approved']);
            $approved = in_array($v, ['1', 'true', 'yes', 'approved'], true) ? 1 : 0;
        }

        $parentId = null;
        if (!empty($data['parent_id'])) {
            $parentId = (int) $data['parent_id'];
        }

        if ($dryRun) {
            if ($existingId) {
                $result['updated']++;
            } else {
                $result['created']++;
            }
            return;
        }

        $comment = $this->commentFactory->create();
        if ($existingId) {
            $comment = $this->commentRepository->getById($existingId);
        }
        $comment->setPostId($postId);
        $comment->setParentId($parentId);
        $comment->setAuthorName($author);
        $comment->setAuthorEmail($data['author_email'] ?? null);
        $comment->setContent($content);
        $comment->setIsApproved($approved);
        if (!empty($data['creation_time'])) {
            $comment->setCreationTime($data['creation_time']);
        }

        $this->commentRepository->save($comment);
        if ($existingId) {
            $result['updated']++;
        } else {
            $result['created']++;
        }
    }

    private function isEmptyRow(array $raw): bool
    {
        foreach ($raw as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }
}

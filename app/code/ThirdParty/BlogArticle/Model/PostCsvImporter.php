<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use ThirdParty\BlogArticle\Api\Data\PostInterfaceFactory;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\Import\WordPressCsvMapper;
use ThirdParty\BlogArticle\Model\PostProductLink;

/**
 * Imports blog posts from a CSV file.
 *
 * Expected header columns (case-insensitive):
 * title*, content*, url_key, author, status, excerpt, category_id, tag_ids,
 * store_id, published_at, meta_title, meta_description, meta_robots, featured_image, product_skus
 *
 * status: enabled|disabled|1|0 (default enabled)
 * tag_ids: comma-separated integers
 * product_skus: comma-separated catalog SKUs or product IDs
 *
 * format=wordpress maps common WP export headers (post_title, post_content, …).
 */
class PostCsvImporter
{
    public const REQUIRED = ['title', 'content'];
    public const FORMAT_NATIVE = 'native';
    public const FORMAT_WORDPRESS = 'wordpress';

    private $csv;
    private $postRepository;
    private $postFactory;
    private $postModelFactory;
    private $wordPressCsvMapper;
    private $postProductLink;

    public function __construct(
        Csv $csv,
        PostRepositoryInterface $postRepository,
        PostInterfaceFactory $postFactory,
        PostFactory $postModelFactory,
        WordPressCsvMapper $wordPressCsvMapper,
        PostProductLink $postProductLink
    ) {
        $this->csv = $csv;
        $this->postRepository = $postRepository;
        $this->postFactory = $postFactory;
        $this->postModelFactory = $postModelFactory;
        $this->wordPressCsvMapper = $wordPressCsvMapper;
        $this->postProductLink = $postProductLink;
    }

    /**
     * @param string $filePath Absolute path to CSV
     * @param bool $dryRun Validate only; do not save
     * @param bool $update Existing posts matched by url_key are updated
     * @param string $format native|wordpress
     * @return array{created:int,updated:int,skipped:int,errors:string[]}
     * @throws LocalizedException
     */
    public function import(
        string $filePath,
        bool $dryRun = false,
        bool $update = false,
        string $format = self::FORMAT_NATIVE
    ): array {
        if (!is_readable($filePath)) {
            throw new LocalizedException(__('CSV file is not readable: %1', $filePath));
        }

        $format = strtolower(trim($format));
        if (!in_array($format, [self::FORMAT_NATIVE, self::FORMAT_WORDPRESS], true)) {
            throw new LocalizedException(__('Unknown import format "%1". Use native or wordpress.', $format));
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
        if ($format === self::FORMAT_WORDPRESS) {
            $header = $this->wordPressCsvMapper->mapHeaders($header);
        }

        foreach (self::REQUIRED as $required) {
            if (!in_array($required, $header, true)) {
                throw new LocalizedException(
                    __(
                        'CSV is missing required column: %1 (format=%2).',
                        $required,
                        $format
                    )
                );
            }
        }

        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $line = 1;
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
                // Later columns with the same mapped name win (e.g. post_date_gmt after post_date).
                $data[$colName] = isset($raw[$colIndex]) ? trim((string) $raw[$colIndex]) : '';
            }
            if ($format === self::FORMAT_WORDPRESS) {
                $data = $this->wordPressCsvMapper->normalizeRow($data);
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
     * @param array $data
     * @param bool $dryRun
     * @param bool $update
     * @param array $result
     * @return void
     * @throws LocalizedException
     */
    private function importRow(array $data, bool $dryRun, bool $update, array &$result): void
    {
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        if ($title === '' || $content === '') {
            throw new LocalizedException(__('title and content are required.'));
        }

        $urlKey = $data['url_key'] ?? '';
        $existingId = null;
        if ($urlKey !== '') {
            $existing = $this->postModelFactory->create()->load($urlKey, 'url_key');
            if ($existing->getId()) {
                if (!$update) {
                    throw new LocalizedException(
                        __('URL key "%1" already exists (use --update to overwrite).', $urlKey)
                    );
                }
                $existingId = (int) $existing->getId();
            }
        }

        $status = strtolower($data['status'] ?? 'enabled');
        if ($status === '') {
            $status = 'enabled';
        }
        if (!in_array($status, ['enabled', 'disabled', '1', '0'], true)) {
            throw new LocalizedException(__('Invalid status "%1".', $status));
        }
        $isActive = in_array($status, ['enabled', '1'], true) ? 1 : 0;

        if ($dryRun) {
            if ($existingId) {
                $result['updated']++;
            } else {
                $result['created']++;
            }
            return;
        }

        $post = $this->postFactory->create();
        if ($existingId) {
            $post->setPostId($existingId);
            // Load existing via repository without active-only so we can update disabled posts
            $loaded = $this->postRepository->getById($existingId, false);
            $post = $loaded;
        }

        $post->setTitle($title);
        $post->setContent($content);
        $post->setIsActive($isActive);
        if ($urlKey !== '') {
            $post->setUrlKey($urlKey);
        } elseif (!$existingId) {
            $post->setUrlKey('');
        }

        if (array_key_exists('author', $data)) {
            $post->setAuthor($data['author'] !== '' ? $data['author'] : null);
        }
        if (array_key_exists('excerpt', $data)) {
            $post->setExcerpt($data['excerpt'] !== '' ? $data['excerpt'] : null);
        }
        if (!empty($data['category_id'])) {
            $post->setCategoryId((int) $data['category_id']);
        } elseif (array_key_exists('category_id', $data) && $data['category_id'] === '') {
            $post->setCategoryId(null);
        }
        if (array_key_exists('store_id', $data) && $data['store_id'] !== '') {
            $post->setStoreId((int) $data['store_id']);
        }
        if (!empty($data['tag_ids'])) {
            $tagIds = array_values(array_filter(array_map('intval', explode(',', $data['tag_ids']))));
            $post->setTagIds($tagIds);
        } elseif (array_key_exists('tag_ids', $data) && $data['tag_ids'] === '') {
            $post->setTagIds([]);
        }
        if (array_key_exists('published_at', $data)) {
            $post->setPublishedAt($data['published_at'] !== '' ? $data['published_at'] : null);
        }
        if (array_key_exists('meta_title', $data)) {
            $post->setMetaTitle($data['meta_title'] !== '' ? $data['meta_title'] : null);
        }
        if (array_key_exists('meta_description', $data)) {
            $post->setMetaDescription($data['meta_description'] !== '' ? $data['meta_description'] : null);
        }
        if (array_key_exists('meta_robots', $data)) {
            $post->setMetaRobots($data['meta_robots'] !== '' ? strtoupper($data['meta_robots']) : null);
        }
        if (array_key_exists('featured_image', $data)) {
            $post->setFeaturedImage($data['featured_image'] !== '' ? $data['featured_image'] : null);
        }

        $this->postRepository->save($post);

        if (array_key_exists('product_skus', $data) && !$dryRun) {
            $savedId = (int) ($post->getPostId() ?: $post->getId());
            if ($savedId > 0) {
                $this->postProductLink->setProductsFromInput($savedId, (string) $data['product_skus']);
            }
        }

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

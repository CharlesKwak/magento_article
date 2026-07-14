<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Exports categories or tags to CSV.
 */
class TaxonomyCsvExporter
{
    public const HEADERS = [
        'id',
        'name',
        'url_key',
        'description',
        'sort_order',
        'status',
        'creation_time',
        'update_time',
    ];

    private Csv $csv;
    private Filesystem $filesystem;

    public function __construct(Csv $csv, Filesystem $filesystem)
    {
        $this->csv = $csv;
        $this->filesystem = $filesystem;
    }

    /**
     * @param AbstractCollection $collection
     * @param string $idField
     * @param string $filePrefix
     * @param string|null $absolutePath
     * @param string $status all|enabled|disabled
     * @return array{path:string,count:int}
     * @throws LocalizedException
     */
    public function export(
        AbstractCollection $collection,
        string $idField,
        string $filePrefix,
        ?string $absolutePath = null,
        string $status = 'all'
    ): array {
        $status = strtolower(trim($status));
        if (!in_array($status, ['all', 'enabled', 'disabled'], true)) {
            throw new LocalizedException(__('Invalid status filter.'));
        }
        if ($status === 'enabled') {
            $collection->addFieldToFilter('is_active', 1);
        } elseif ($status === 'disabled') {
            $collection->addFieldToFilter('is_active', 0);
        }
        $collection->setOrder('name', 'ASC');

        $rows = [self::HEADERS];
        foreach ($collection as $item) {
            $rows[] = [
                (string) $item->getData($idField),
                (string) $item->getName(),
                (string) $item->getUrlKey(),
                (string) $item->getData('description'),
                (string) (int) $item->getData('sort_order'),
                (int) $item->getIsActive() ? 'enabled' : 'disabled',
                (string) $item->getCreationTime(),
                (string) $item->getUpdateTime(),
            ];
        }

        $path = $this->resolvePath($absolutePath, $filePrefix);
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

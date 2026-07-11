<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Uploads featured images into pub/media/blogarticle/.
 */
class FeaturedImageUploader
{
    public const MEDIA_PATH = 'blogarticle';

    private $uploaderFactory;
    private $filesystem;
    private $storeManager;

    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $fileId form file field name
     * @return string|null Absolute media URL or null if no file
     * @throws LocalizedException
     */
    public function upload(string $fileId = 'featured_image_file'): ?string
    {
        if (empty($_FILES[$fileId]['name'])) {
            return null;
        }

        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'webp']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);

            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $target = $mediaDir->getAbsolutePath(self::MEDIA_PATH);
            $result = $uploader->save($target);
            if (!$result || empty($result['file'])) {
                throw new LocalizedException(__('Image upload failed.'));
            }

            $relative = self::MEDIA_PATH . '/' . ltrim(str_replace('\\', '/', $result['file']), '/');
            return $this->toMediaUrl($relative);
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not upload image: %1', $e->getMessage()), $e);
        }
    }

    /**
     * Resolve stored value (absolute URL or media-relative path) to a public URL.
     *
     * @param string|null $value
     * @return string
     */
    public function resolveUrl(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }
        $relative = preg_replace('#^/?media/#', '', $value);
        $relative = ltrim((string) $relative, '/');
        return $this->toMediaUrl($relative);
    }

    /**
     * @param string $relativePath under media
     * @return string
     */
    private function toMediaUrl(string $relativePath): string
    {
        $base = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return rtrim($base, '/') . '/' . ltrim($relativePath, '/');
    }
}

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
    private $config;

    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->config = $config;
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

        $maxBytes = $this->config->getMaxUploadKb() * 1024;
        if (!empty($_FILES[$fileId]['size']) && (int) $_FILES[$fileId]['size'] > $maxBytes) {
            throw new LocalizedException(
                __('Image is too large. Maximum size is %1 KB.', $this->config->getMaxUploadKb())
            );
        }

        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'webp']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
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
     * Local filesystem path for a media-relative image (empty for remote URLs).
     */
    public function resolveLocalPath(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '' || preg_match('#^https?://#i', $value)) {
            return '';
        }
        $relative = preg_replace('#^/?media/#', '', $value);
        $relative = ltrim((string) $relative, '/');
        if ($relative === '') {
            return '';
        }
        $mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $absolute = $mediaDir->getAbsolutePath($relative);
        return is_file($absolute) ? $absolute : '';
    }

    /**
     * @return array{width:?int,height:?int}
     */
    public function getImageDimensions(?string $value): array
    {
        $path = $this->resolveLocalPath($value);
        if ($path === '') {
            return ['width' => null, 'height' => null];
        }
        $size = @getimagesize($path);
        if (!is_array($size) || empty($size[0]) || empty($size[1])) {
            return ['width' => null, 'height' => null];
        }
        return [
            'width' => (int) $size[0],
            'height' => (int) $size[1],
        ];
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

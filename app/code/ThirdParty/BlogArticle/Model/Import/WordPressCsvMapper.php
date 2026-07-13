<?php
namespace ThirdParty\BlogArticle\Model\Import;

/**
 * Maps common WordPress / export-plugin CSV column names to Blog Article fields.
 */
class WordPressCsvMapper
{
    /**
     * Source header (lowercase) => canonical field.
     */
    private const ALIASES = [
        'title' => 'title',
        'post_title' => 'title',
        'name' => 'title',
        'content' => 'content',
        'post_content' => 'content',
        'body' => 'content',
        'url_key' => 'url_key',
        'post_name' => 'url_key',
        'slug' => 'url_key',
        'permalink' => 'url_key',
        'author' => 'author',
        'post_author' => 'author',
        'author_name' => 'author',
        'status' => 'status',
        'post_status' => 'status',
        'excerpt' => 'excerpt',
        'post_excerpt' => 'excerpt',
        'category_id' => 'category_id',
        'categories' => 'category_names',
        'category' => 'category_names',
        'tag_ids' => 'tag_ids',
        'tags' => 'tag_names',
        'post_tag' => 'tag_names',
        'store_id' => 'store_id',
        'published_at' => 'published_at',
        'post_date' => 'published_at',
        'post_date_gmt' => 'published_at',
        'date' => 'published_at',
        'meta_title' => 'meta_title',
        'seo_title' => 'meta_title',
        '_yoast_wpseo_title' => 'meta_title',
        'meta_description' => 'meta_description',
        'seo_description' => 'meta_description',
        '_yoast_wpseo_metadesc' => 'meta_description',
        'featured_image' => 'featured_image',
        'image' => 'featured_image',
        'thumbnail' => 'featured_image',
        'featured_image_url' => 'featured_image',
    ];

    /**
     * Normalize a header cell to a canonical field name (or empty if unknown).
     */
    public function mapHeader(string $header): string
    {
        $key = strtolower(trim($header));
        $key = ltrim($key, "\xEF\xBB\xBF"); // UTF-8 BOM
        return self::ALIASES[$key] ?? $key;
    }

    /**
     * @param string[] $headers Raw header row
     * @return string[] Canonical headers (same length; unknowns lowercased)
     */
    public function mapHeaders(array $headers): array
    {
        $mapped = [];
        foreach ($headers as $h) {
            $mapped[] = $this->mapHeader((string) $h);
        }
        return $mapped;
    }

    /**
     * Normalize WordPress status / date quirks after column mapping.
     *
     * @param array<string, string> $row
     * @return array<string, string>
     */
    public function normalizeRow(array $row): array
    {
        if (isset($row['status'])) {
            $status = strtolower(trim((string) $row['status']));
            if (in_array($status, ['publish', 'published', 'public'], true)) {
                $row['status'] = 'enabled';
            } elseif (in_array($status, ['draft', 'pending', 'private', 'future', 'trash'], true)) {
                $row['status'] = 'disabled';
            }
        }

        if (!empty($row['published_at'])) {
            $row['published_at'] = $this->normalizeDate((string) $row['published_at']);
        }

        // Prefer post_name-style slugs already in url_key; strip site URL if a full permalink was mapped.
        if (!empty($row['url_key']) && preg_match('#https?://#i', $row['url_key'])) {
            $path = parse_url($row['url_key'], PHP_URL_PATH);
            if (is_string($path) && $path !== '') {
                $slug = trim(basename(rtrim($path, '/')), '/');
                if ($slug !== '') {
                    $row['url_key'] = $slug;
                }
            }
        }

        // category_names / tag_names are hints only (IDs still preferred); leave for future use.
        unset($row['category_names'], $row['tag_names']);

        return $row;
    }

    private function normalizeDate(string $value): string
    {
        $value = trim($value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return $value;
        }
        return gmdate('Y-m-d H:i:s', $ts);
    }
}

<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental\Public;

use DateTimeImmutable;
use InvalidArgumentException;
use Lemonade\Admin\File\FileMetadata;
use Lemonade\Admin\File\FileReadModel;
use Lemonade\Cms\Content\PublishedContentScope;
use Lemonade\Cms\Rental\CmsRentalServiceProvider;
use Lemonade\Cms\Rental\ModuleManifest;
use Lemonade\Cms\Routing\Cms\PublicCmsUrlBuilder;
use Lemonade\Cms\Routing\Locale\PublicLocaleResolution;
use Lemonade\Cms\Routing\Module\PublicModuleStateResolverInterface;
use Lemonade\Framework\Database\Database;
use Lemonade\Framework\Database\QueryBuilder;

/**
 * Reads publicly available rentals through one shared publication scope
 */
final readonly class PublishedRentalReadModel
{
    /**
     * Creates the rental reader with CMS routing and shared file metadata
     */
    public function __construct(
        private Database $database,
        private FileReadModel $files,
        private PublicCmsUrlBuilder $urls,
        private PublicModuleStateResolverInterface $modules,
    ) {}

    /**
     * Returns one visible rental translation or null when it is unavailable
     */
    public function record(int $id, PublicLocaleResolution $locale): ?PublishedRental
    {
        $language = $this->visibleLocale($locale);
        if ($language === null) {
            return null;
        }

        $row = $this->publishedQuery($language)->where('article.id', $id)->first();

        return $row === null ? null : $this->hydrate([$row], $locale)[0];
    }

    /**
     * Returns a page filtered by localized tag, rental state and recommendation
     */
    public function collection(
        PublicLocaleResolution $locale,
        int $page = 1,
        int $perPage = 25,
        ?string $tag = null,
        ?string $offerState = null,
        ?bool $recommended = null,
    ): PublishedRentalCollection {
        if ($page < 1 || $perPage < 1) {
            throw new InvalidArgumentException('Page and page size must be positive.');
        }

        $language = $this->visibleLocale($locale);
        if ($language === null) {
            return new PublishedRentalCollection([], 0, $page, $perPage);
        }

        $query = $this->withFilters($this->publishedQuery($language), $tag, $offerState, $recommended);
        $total = $query->countAllResults();
        if ($total === 0 || $page - 1 > intdiv($total - 1, $perPage)) {
            return new PublishedRentalCollection([], $total, $page, $perPage);
        }

        $rows = $query->orderBy('translation.published_at', 'DESC')
            ->orderBy('article.id', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->getArray();

        return new PublishedRentalCollection($this->hydrate($rows, $locale), $total, $page, $perPage);
    }

    /**
     * Returns the newest visible rentals without an unnecessary count query
     *
     * @return list<PublishedRental>
     */
    public function latest(
        PublicLocaleResolution $locale,
        int $limit = 5,
        ?string $tag = null,
    ): array {
        return $this->recent($locale, $limit, $tag, recommended: null, excludedId: null);
    }

    /**
     * Returns the newest visible rentals except the current record
     *
     * @return list<PublishedRental>
     */
    public function others(int $id, PublicLocaleResolution $locale, int $limit = 5, ?string $tag = null): array
    {
        return $this->recent($locale, $limit, $tag, recommended: null, excludedId: $id);
    }

    /**
     * Returns the newest visible rentals marked as recommended
     *
     * @return list<PublishedRental>
     */
    public function recommended(PublicLocaleResolution $locale, int $limit = 5, ?string $tag = null): array
    {
        return $this->recent($locale, $limit, $tag, recommended: true, excludedId: null);
    }

    /**
     * Yields visible rental candidates with their canonical URL and last public update
     *
     * @return iterable<PublishedRentalSitemapEntry>
     */
    public function sitemapEntries(PublicLocaleResolution $locale): iterable
    {
        $language = $this->visibleLocale($locale);
        if ($language === null) {
            return;
        }

        $query = $this->publishedScope($language)
            ->select(['route.path'])
            ->selectRaw('GREATEST(article.updated_at, translation.updated_at, route.updated_at) AS last_modified_at')
            ->orderBy('article.id', 'ASC');

        foreach ($query->cursor() as $row) {
            yield new PublishedRentalSitemapEntry(
                $this->urls->build($locale, $language, (string) $row['path']),
                new DateTimeImmutable((string) $row['last_modified_at']),
            );
        }
    }

    /**
     * Reports whether a rental is publicly visible in any enabled locale
     */
    public function isPublicInAnyLocale(int $id): bool
    {
        if (!$this->modules->isDiscoveredInstalledAndEnabled(ModuleManifest::CODE)) {
            return false;
        }

        return $this->publishedScope()->where('article.id', $id)->countAllResults() > 0;
    }

    /**
     * Restricts a recent list by tag, recommendation and an excluded rental
     *
     * @return list<PublishedRental>
     */
    private function recent(
        PublicLocaleResolution $locale,
        int $limit,
        ?string $tag,
        ?bool $recommended,
        ?int $excludedId,
    ): array {
        if ($limit < 1) {
            throw new InvalidArgumentException('Rental limit must be positive.');
        }

        $language = $this->visibleLocale($locale);
        if ($language === null) {
            return [];
        }

        $query = $this->withFilters($this->publishedQuery($language), $tag, null, $recommended);
        if ($excludedId !== null) {
            $query = $query->where('article.id !=', $excludedId);
        }
        $rows = $query
            ->orderBy('translation.published_at', 'DESC')
            ->orderBy('article.id', 'DESC')
            ->limit($limit)
            ->getArray();

        return $this->hydrate($rows, $locale);
    }

    /**
     * Returns the requested public locale when the rental module is available
     */
    private function visibleLocale(PublicLocaleResolution $locale): ?string
    {
        $language = $locale->getLocale();

        return $language !== null && $this->modules->isDiscoveredInstalledAndEnabled(ModuleManifest::CODE)
            ? $language
            : null;
    }

    /**
     * Applies optional content filters to the shared publication query
     */
    private function withFilters(
        QueryBuilder $query,
        ?string $tag,
        ?string $offerState,
        ?bool $recommended,
    ): QueryBuilder {
        if ($tag !== null) {
            $query = $query->whereRaw(
                'EXISTS (SELECT 1 FROM cms_rental_translation_tag tag WHERE tag.translation_id = translation.id AND tag.name = ?)',
                [$tag],
            );
        }
        if ($recommended !== null) {
            $query = $query->where('article.recommended', $recommended ? 1 : 0);
        }
        if ($offerState !== null) {
            $query = $query->where('article.offer_state', $offerState);
        }

        return $query;
    }

    /**
     * Selects the system and domain content needed for published rentals
     */
    private function publishedQuery(string $language): QueryBuilder
    {
        return $this->publishedScope($language)
            ->select([
                'article.id', 'article.author_name', 'article.recommended',
                'article.show_author', 'article.sharing_enabled', 'article.show_published_at',
                'article.show_thumbnail', 'article.show_reading_time', 'article.show_tags',
                'article.monthly_rent', 'article.currency', 'article.available_from',
                'article.offer_state', 'article.external_url',
                'translation.locale', 'translation.title', 'translation.summary', 'translation.content',
                'translation.page_title', 'translation.meta_description', 'translation.location',
                'translation.published_at', 'translation.updated_at',
            ])
            ->selectRaw('translation.id AS translation_id, route.path AS canonical_path');
    }

    /**
     * Applies the shared public visibility rules for routed Rental content
     */
    private function publishedScope(?string $language = null): QueryBuilder
    {
        return PublishedContentScope::query(
            $this->database,
            CmsRentalServiceProvider::contentDefinition(),
            $language,
        );
    }

    /**
     * Hydrates visible rentals with one tag query and one shared file query
     *
     * @param list<array<string,mixed>> $rows
     * @return list<PublishedRental>
     */
    private function hydrate(array $rows, PublicLocaleResolution $locale): array
    {
        if ($rows === []) {
            return [];
        }

        $translationIds = array_map(static fn(array $row): int => (int) $row['translation_id'], $rows);
        $tags = [];
        foreach ($this->database->table('cms_rental_translation_tag tag')
            ->select(['tag.translation_id', 'tag.name'])
            ->whereIn('tag.translation_id', $translationIds)
            ->orderBy('tag.translation_id')
            ->orderBy('tag.sort_order')
            ->orderBy('tag.id')
            ->getArray() as $tag) {
            $tags[(int) $tag['translation_id']][] = (string) $tag['name'];
        }

        $ids = array_map(static fn(array $row): int => (int) $row['id'], $rows);
        $media = [];
        foreach ($this->files->listForEntitiesUsages(ModuleManifest::CODE, $ids, ['thumbnail', 'gallery', 'attachment']) as $file) {
            if (!$this->isReadableFile($file)) {
                continue;
            }
            $media[$file->entityId()][$file->usage()][] = $file;
        }

        $rentals = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $files = $media[$id] ?? [];
            $rentals[] = new PublishedRental(
                id: $id,
                locale: (string) $row['locale'],
                title: (string) $row['title'],
                summary: $row['summary'] === null ? null : (string) $row['summary'],
                content: $row['content'] === null ? null : (string) $row['content'],
                pageTitle: $row['page_title'] === null ? null : (string) $row['page_title'],
                metaDescription: $row['meta_description'] === null ? null : (string) $row['meta_description'],
                publishedAt: new DateTimeImmutable((string) $row['published_at']),
                updatedAt: new DateTimeImmutable((string) $row['updated_at']),
                publicUrl: $this->urls->build($locale, (string) $row['locale'], (string) $row['canonical_path']),
                location: $row['location'] === null ? null : (string) $row['location'],
                authorName: $row['author_name'] === null ? null : (string) $row['author_name'],
                monthlyRent: $row['monthly_rent'] === null ? null : (string) $row['monthly_rent'],
                currency: (string) $row['currency'],
                availableFrom: $row['available_from'] === null ? null : new DateTimeImmutable((string) $row['available_from']),
                offerState: (string) $row['offer_state'],
                externalUrl: $this->validHttpUrl($row['external_url']),
                tags: $tags[(int) $row['translation_id']] ?? [],
                recommended: (bool) $row['recommended'],
                showAuthor: (bool) $row['show_author'],
                sharingEnabled: (bool) $row['sharing_enabled'],
                showPublishedAt: (bool) $row['show_published_at'],
                showThumbnail: (bool) $row['show_thumbnail'],
                showReadingTime: (bool) $row['show_reading_time'],
                showTags: (bool) $row['show_tags'],
                thumbnail: $files['thumbnail'][0] ?? null,
                gallery: $files['gallery'] ?? [],
                attachments: $files['attachment'] ?? [],
            );
        }

        return $rentals;
    }

    /**
     * Returns only valid HTTP and HTTPS rental links from stored data
     */
    private function validHttpUrl(mixed $value): ?string
    {
        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true)
            ? $value
            : null;
    }

    /**
     * Checks the backing data required to make one stored file publicly readable
     */
    private function isReadableFile(FileMetadata $file): bool
    {
        if (in_array($file->usage(), ['thumbnail', 'gallery'], true) && $file->kind() !== 'image') {
            return false;
        }
        if ($file->kind() === 'image') {
            return $file->assetId() !== null && $file->sourceVersion() !== null;
        }

        return $file->kind() === 'file' && $file->storagePath() !== null;
    }
}

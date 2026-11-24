<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental\Public;

use DateTimeImmutable;

/**
 * Provides a visible canonical rental candidate for a host sitemap
 */
final readonly class PublishedRentalSitemapEntry
{
    /**
     * Creates a sitemap candidate with its last public update
     */
    public function __construct(
        private string $publicUrl,
        private DateTimeImmutable $updatedAt,
    ) {}

    /**
     * Returns the localized canonical URL path
     */
    public function getPublicUrl(): string
    {
        return $this->publicUrl;
    }

    /**
     * Returns the latest change to the rental, translation or canonical route
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

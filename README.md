# Lemonade CMS Rental

[![Tests](https://img.shields.io/github/actions/workflow/status/johnnyxlemonade/admin-cms-rental/phpunit.yml?branch=main&label=Tests&logo=githubactions&logoColor=white&labelColor=0f766e)](https://github.com/johnnyxlemonade/admin-cms-rental/actions/workflows/phpunit.yml)
[![PHP 8.3–8.6](https://img.shields.io/badge/PHP-8.3%20%7C%208.4%20%7C%208.5%20%7C%208.6-777BB4?logo=php&logoColor=white)](#requirements)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/johnnyxlemonade/admin-cms-rental/phpstan.yml?branch=main&label=PHPStan&logo=php&logoColor=white&labelColor=5b4baf)](https://github.com/johnnyxlemonade/admin-cms-rental/actions/workflows/phpstan.yml)
[![License](https://img.shields.io/badge/license-Apache--2.0-green.svg)](LICENSE)

`johnnyxlemonade/admin-cms-rental` is an optional CMS module with the
`cms.rental` code. When present in the Composer installation, it provides
rental management at `<admin-base>/cms/rental` (for the standard `/admin`
base path, `/admin/cms/rental`).

A rental root stores its author name, owner, monthly rent text with currency code, availability
date, offer state, external URL, and shared display settings. The author name
is text metadata; the owner is a separate audit identity. Each localized
translation stores its location, title, summary, content, SEO metadata, tags,
and publication state and time. Display settings are root metadata shared
across translations: recommendation, author, sharing, publication date, main
image, reading time, and tags.

The module uses the shared CMS content lifecycle for drafts, audited saves,
soft deletion, restoration, and canonical URL reservations. Images, galleries,
and attachments use Admin Platform file collections. The host owns public
controllers, templates, HTML, design, presentation-specific calculations and
the framework sitemap provider.

## Public rentals

`CmsRentalServiceProvider` registers `PublishedRentalReadModel`. `record()`
returns one visible translation or `null`; `collection()` returns a typed
`PublishedRentalCollection`. `latest()` returns recent rentals without a count
query. `others()` returns recent rentals except the current record for detail pages.
`recommended()` returns recent records marked as recommended. Lists support a
localized tag; `collection()` also supports offer business state and
recommendation filters. The business state (`preparing`, `available`, `reserved`, `rented`,
`cancelled`) never replaces the separate CMS publication rules.

```php
use Lemonade\Cms\Rental\Public\PublishedRentalReadModel;

$rental = $rentals->record($id, $locale);
$page = $rentals->collection($locale, page: $pageNumber, perPage: 12, tag: $tag);
$recent = $rentals->others($id, $locale, limit: 2);
$navigation = $paginationComponent->render($page->getPagination(
    basePath: $request->getUri()->getPath(),
    query: ['tag' => $tag],
));

$priceText = $rental?->getMonthlyRent();
$currency = $rental?->getCurrency();
$availableFrom = $rental?->getAvailableFrom();
$businessState = $rental?->getOfferState();
$gallery = $rental?->getGallery() ?? [];
$attachments = $rental?->getAttachments() ?? [];
```

The monthly rent remains stored text, not a numeric amount. `getAvailableFrom()`
returns `?DateTimeImmutable`; the host formats the date and interprets the
business state for presentation. `PublishedRental` also exposes localized
summary, content, SEO, location, author, tags, display settings and canonical
URL through typed getters. It implements `PublishedMediaInterface` with
`getThumbnail()`, ordered `getGallery()` and ordered `getAttachments()` based on
batched `FileReadModel` metadata. The host creates presentation URLs for files.
`isPublicInAnyLocale($id)` lets a host download handler check the public
availability of an attachment owner.

All public queries require an available module, enabled language, published
translation with a reached publication time, nondeleted rental and active
canonical route. `collection()` uses filtered SQL `COUNT` and `LIMIT/OFFSET`;
it never loads the whole result set for pagination. Tags and files are loaded
in batches.

`PublishedRentalReadModel::sitemapEntries($locale)` yields lightweight
`PublishedRentalSitemapEntry` candidates with `getPublicUrl()` and
`getUpdatedAt()`. The update time is the latest root, translation or canonical
route change. The host selects locales and candidates in its existing framework
`sitemap.providers` registration and passes them to `SitemapUrl`:

```php
use Lemonade\Framework\Discovery\Sitemap\SitemapUrl;

foreach ($rentals->sitemapEntries($locale) as $entry) {
    yield SitemapUrl::create($entry->getPublicUrl(), $entry->getUpdatedAt());
}
```

## Migrating a host reader

Replace host-owned SQL publication queries with `record()`, `collection()`,
`latest()`, and `isPublicInAnyLocale()`. A host listing controller can pass
`collection()->getItems()` to its view and render `getPagination()` with its
existing `PaginationComponent`. A detail controller can use `record()` and
`others($id, $locale)`. Keep host templates, image URLs, date and
rent formatting, reading-time calculations, and controller behavior in the
host. For an attachment download, retain the existing file authorization check
and use `isPublicInAnyLocale()` for the content owner. Map the new standard
`get...()` and `is...()` accessors when replacing an existing host DTO.

## Requirements

- PHP 8.3 or newer, tested on PHP 8.3, 8.4, 8.5 and 8.6.
- `johnnyxlemonade/framework:dev-main`.
- `johnnyxlemonade/admin-platform:dev-main`.
- `johnnyxlemonade/cms-core:dev-main`.

## Installation

```bash
composer require johnnyxlemonade/admin-cms-rental:dev-main
```

Composer package metadata discovers and activates the module; there is no
separate installed or enabled state. Run the host application's controlled
migration and permission-sync deployment flow before serving requests.
Permissions are not granted to roles automatically.

## Package boundaries

- CMS Rental owns the rental aggregate, Admin module contribution,
  publication workflow, and rental-specific permissions.
- Admin Platform owns shared editor components, file collections, upload
  transport, and `system_file` metadata.
- CMS Core owns the shared content lifecycle and canonical route reservations.
- The host owns public routes, rendering, and sitemap selection.

## Development and QA

From the package root, run:

```bash
composer cs:check
composer stan
composer test
composer qa
```

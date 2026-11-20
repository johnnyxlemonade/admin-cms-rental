# Lemonade CMS Rental

[![Tests](https://img.shields.io/github/actions/workflow/status/johnnyxlemonade/admin-cms-rental/phpunit.yml?branch=main&label=Tests&logo=githubactions&logoColor=white&labelColor=0f766e)](https://github.com/johnnyxlemonade/admin-cms-rental/actions/workflows/phpunit.yml)
[![PHP 8.3–8.6](https://img.shields.io/badge/PHP-8.3%20%7C%208.4%20%7C%208.5%20%7C%208.6-777BB4?logo=php&logoColor=white)](#requirements)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/johnnyxlemonade/admin-cms-rental/phpstan.yml?branch=main&label=PHPStan&logo=php&logoColor=white&labelColor=5b4baf)](https://github.com/johnnyxlemonade/admin-cms-rental/actions/workflows/phpstan.yml)
[![License](https://img.shields.io/badge/license-Apache--2.0-green.svg)](LICENSE)

`johnnyxlemonade/admin-cms-rental` is an optional CMS module with the
`cms.rental` code. When present in the Composer installation, it provides
rental management at `<admin-base>/cms/rental` (for the standard `/admin`
base path, `/admin/cms/rental`).

A rental root stores its author name, owner, monthly rent in CZK, availability
date, offer state, external URL, and shared display settings. The author name
is text metadata; the owner is a separate audit identity. Each localized
translation stores its location, title, summary, content, SEO metadata, tags,
and publication state and time. Display settings are root metadata shared
across translations: recommendation, author, sharing, publication date, main
image, reading time, and tags.

The module uses the shared CMS content lifecycle for drafts, audited saves,
soft deletion, restoration, and canonical URL reservations. Images, galleries,
and attachments use Admin Platform file collections. The package does not
provide public routes, templates, or rental rendering; the host application
decides how to use the stored display metadata.

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
- The host owns public routes, rendering, and publication checks.

## Local Portal integration

For a local Portal checkout, add a Composer path repository pointing to
`../admin-cms-rental` and require
`johnnyxlemonade/admin-cms-rental:dev-main` in the local Composer manifest.
The distribution manifest and lock file remain unchanged.

## Development and QA

From the package root, run:

```bash
composer cs:check
composer stan
composer test
composer qa
```

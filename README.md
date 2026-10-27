# Lemonade CMS Rental

[![PHPStan](https://github.com/johnnyxlemonade/admin-cms-rental/actions/workflows/phpstan.yml/badge.svg)](https://github.com/johnnyxlemonade/admin-cms-rental/actions/workflows/phpstan.yml)
[![Tests](https://github.com/johnnyxlemonade/admin-cms-rental/actions/workflows/phpunit.yml/badge.svg)](https://github.com/johnnyxlemonade/admin-cms-rental/actions/workflows/phpunit.yml)
[![Coding Standards](https://github.com/johnnyxlemonade/admin-cms-rental/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/johnnyxlemonade/admin-cms-rental/actions/workflows/coding-standards.yml)
[![License](https://img.shields.io/badge/license-Apache--2.0-blue.svg)](composer.json)

`johnnyxlemonade/admin-cms-rental` is an optional CMS module with the
`cms.rental` code. When present in the Composer installation, it provides
rental management at `<admin-base>/content/rental` (for the standard `/admin`
base path, `/admin/content/rental`).

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

## Installation

The package requires PHP `>=8.3 <8.6`, `johnnyxlemonade/framework`,
`johnnyxlemonade/admin-platform`, and `johnnyxlemonade/cms-core`.

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

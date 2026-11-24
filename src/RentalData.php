<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental;

use Lemonade\Cms\Content\CmsContentValidation;
use Lemonade\Cms\Content\CmsLocalized;
use Lemonade\Cms\Content\CmsRoot;

/**
 * Represents root and localized domain data of one rental
 */
final readonly class RentalData
{
    /**
     * Creates the rental data stored by the shared CMS lifecycle
     */
    public function __construct(
        #[CmsRoot('author_name')]
        private ?string $authorName,
        #[CmsRoot('monthly_rent')]
        private ?string $monthlyRent,
        #[CmsRoot('currency')]
        private string $currency,
        #[CmsRoot('available_from')]
        private ?string $availableFrom,
        #[CmsRoot('offer_state')]
        private string $offerState,
        #[CmsRoot('external_url')]
        private ?string $externalUrl,
        #[CmsLocalized('location')]
        private ?string $location,
    ) {}

    /**
     * Returns the optional author name
     */
    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    /**
     * Returns the stored monthly rent text
     */
    public function getMonthlyRent(): ?string
    {
        return $this->monthlyRent;
    }

    /**
     * Returns the stored currency code
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Returns the optional availability date
     */
    public function getAvailableFrom(): ?string
    {
        return $this->availableFrom;
    }

    /**
     * Returns the offer state independently of CMS publication
     */
    public function getOfferState(): string
    {
        return $this->offerState;
    }

    /**
     * Returns the optional external rental URL
     */
    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    /**
     * Returns the localized rental location
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Validates and normalizes domain values submitted by the editor
     *
     * @param array<string,mixed> $data
     */
    public static function fromEditor(array $data): self
    {
        $data = CmsContentValidation::texts($data, ['author_name', 'monthly_rent', 'available_from', 'offer_state', 'external_url', 'location']);
        $errors = [];
        $author = $data['author_name'];
        if ($author !== null && mb_strlen($author) > 255) {
            $errors['author_name'] = 'rental.validation.author_max_length';
        }
        $price = $data['monthly_rent'];
        if ($price !== null && (mb_strlen($price) > 150
            || preg_match('/^[−-]\s*[0-9]|[\x00-\x1F\x7F]/u', $price) === 1)) {
            $errors['monthly_rent'] = 'rental.validation.price_invalid';
        }
        $state = (string) ($data['offer_state'] ?? 'preparing');
        if (!in_array($state, ['preparing', 'available', 'reserved', 'rented', 'cancelled'], true)) {
            $errors['offer_state'] = 'rental.validation.state_invalid';
        }
        $url = $data['external_url'];
        if ($url !== null && (mb_strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false || !in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true))) {
            $errors['external_url'] = 'rental.validation.url_invalid';
        }
        $availableFrom = $data['available_from'];
        if ($availableFrom !== null && !CmsContentValidation::validDate($availableFrom, false)) {
            $errors['available_from'] = 'rental.validation.date_invalid';
        }
        $location = $data['location'];
        if ($location !== null && mb_strlen($location) > 255) {
            $errors['location'] = 'rental.validation.location_invalid';
        }

        CmsContentValidation::assert($errors);

        return new self(
            authorName: $author,
            monthlyRent: $price,
            currency: 'CZK',
            availableFrom: $availableFrom,
            offerState: $state,
            externalUrl: $url,
            location: $location,
        );
    }
}

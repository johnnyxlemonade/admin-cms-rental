<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental;

use DateTimeImmutable;
use DomainException;
use Lemonade\Cms\Content\CmsLocalized;
use Lemonade\Cms\Content\CmsRoot;

/**
 * Predstavuje globalni a lokalizovane domenove udaje pronajmu
 */
final readonly class RentalData
{
    /**
     * Nastavuje autora, cenu, termin, stav, odkaz a lokalitu
     */
    public function __construct(
        #[CmsRoot('author_name')]
        public ?string $authorName,
        #[CmsRoot('monthly_rent', decimalScale: 2)]
        public ?string $monthlyRent,
        #[CmsRoot('currency')]
        public string $currency,
        #[CmsRoot('available_from')]
        public ?string $availableFrom,
        #[CmsRoot('offer_state')]
        public string $offerState,
        #[CmsRoot('external_url')]
        public ?string $externalUrl,
        #[CmsLocalized('location')]
        public ?string $location,
    ) {}

    /**
     * Validuje a normalizuje domenove hodnoty editoru
     *
     * @param array<string,mixed> $data
     */
    public static function fromEditor(array $data): self
    {
        $author = self::nullableText($data['author_name'] ?? null);
        if ($author !== null && mb_strlen($author) > 255) {
            throw new DomainException('rental.validation.author_max_length');
        }
        if (isset($data['monthly_rent']) && !is_string($data['monthly_rent']) && !is_int($data['monthly_rent'])) {
            throw new DomainException('rental.validation.price_invalid');
        }
        $price = self::nullableText($data['monthly_rent'] ?? null);
        if ($price !== null) {
            if (preg_match('/^(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?$/D', $price) !== 1) {
                throw new DomainException('rental.validation.price_invalid');
            }
            [$whole, $fraction] = array_pad(explode('.', $price, 2), 2, '');
            $price = $whole . '.' . str_pad($fraction, 2, '0');
        }
        $state = (string) ($data['offer_state'] ?? 'preparing');
        if (!in_array($state, ['preparing', 'available', 'reserved', 'rented', 'cancelled'], true)) {
            throw new DomainException('rental.validation.state_invalid');
        }
        $url = self::nullableText($data['external_url'] ?? null);
        if ($url !== null && (mb_strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false || !in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true))) {
            throw new DomainException('rental.validation.url_invalid');
        }
        $availableFrom = self::nullableText($data['available_from'] ?? null);
        if ($availableFrom !== null) {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $availableFrom);
            if ($date === false || $date->format('Y-m-d') !== $availableFrom || (int) substr($availableFrom, 0, 4) < 1000) {
                throw new DomainException('rental.validation.date_invalid');
            }
            $availableFrom = $date->format('Y-m-d');
        }
        $location = self::nullableText($data['location'] ?? null);
        if ($location !== null && mb_strlen($location) > 255) {
            throw new DomainException('rental.validation.location_invalid');
        }

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

    /**
     * Prevede prazdny volitelny text na null
     */
    private static function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}

<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental\Tests\Unit;

use DomainException;
use Lemonade\Cms\Content\CmsDomainMapping;
use Lemonade\Cms\Rental\RentalData;
use PHPUnit\Framework\TestCase;

/**
 * Overuje typovane mapovani a validaci hodnot pronajmu
 */
final class RentalDataTest extends TestCase
{
    public function testMapsDecimalAndLocalizedLocation(): void
    {
        $data = RentalData::fromEditor([
            'monthly_rent' => '1234567890123.5',
            'available_from' => '2028-02-29',
            'offer_state' => 'available',
            'external_url' => 'https://example.com/rental',
            'location' => ' Praha ',
        ]);
        self::assertSame([
            'root' => [
                'author_name' => null,
                'monthly_rent' => '1234567890123.50',
                'currency' => 'CZK',
                'available_from' => '2028-02-29',
                'offer_state' => 'available',
                'external_url' => 'https://example.com/rental',
            ],
            'localized' => ['location' => 'Praha'],
        ], (new CmsDomainMapping(RentalData::class))->values($data));
    }

    /**
     * @dataProvider invalidValues
     * @param array<string,mixed> $data
     */
    public function testRejectsInvalidValuesWithExistingKey(array $data, string $key): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage($key);
        RentalData::fromEditor($data);
    }

    /**
     * @return iterable<string,array{array<string,mixed>,string}>
     */
    public static function invalidValues(): iterable
    {
        yield 'float' => [['monthly_rent' => 1.23], 'rental.validation.price_invalid'];
        yield 'overflow' => [['monthly_rent' => '10000000000000.00'], 'rental.validation.price_invalid'];
        yield 'negative' => [['monthly_rent' => '-1'], 'rental.validation.price_invalid'];
        yield 'date range' => [['available_from' => '0000-01-01'], 'rental.validation.date_invalid'];
        yield 'price' => [['monthly_rent' => '1.001'], 'rental.validation.price_invalid'];
        yield 'date' => [['available_from' => '2026-02-30'], 'rental.validation.date_invalid'];
        yield 'state' => [['offer_state' => 'published'], 'rental.validation.state_invalid'];
        yield 'url' => [['external_url' => 'javascript:alert(1)'], 'rental.validation.url_invalid'];
        yield 'location' => [['location' => str_repeat('x', 256)], 'rental.validation.location_invalid'];
    }
}

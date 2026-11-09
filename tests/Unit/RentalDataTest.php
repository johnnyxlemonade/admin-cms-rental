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
    public function testMapsTextRentAndLocalizedLocation(): void
    {
        $data = RentalData::fromEditor([
            'monthly_rent' => ' 13 000 Kč ',
            'available_from' => '2028-02-29',
            'offer_state' => 'available',
            'external_url' => 'https://example.com/rental',
            'location' => ' Praha ',
        ]);
        self::assertSame([
            'root' => [
                'author_name' => null,
                'monthly_rent' => '13 000 Kč',
                'currency' => 'CZK',
                'available_from' => '2028-02-29',
                'offer_state' => 'available',
                'external_url' => 'https://example.com/rental',
            ],
            'localized' => ['location' => 'Praha'],
        ], (new CmsDomainMapping(RentalData::class))->values($data));
    }

    /**
     * Nepovinna cena zustava textova a rozpracovana data mohou byt prazdna
     */
    public function testOptionalTextPriceAndEmptyDomainValuesRemainValid(): void
    {
        foreach (['', '400 000 Kč', 'Cena na vyžádání'] as $price) {
            $data = RentalData::fromEditor(['monthly_rent' => $price, 'available_from' => '', 'offer_state' => '', 'location' => '', 'external_url' => '']);
            $values = (new CmsDomainMapping(RentalData::class))->values($data);
            self::assertSame($price === '' ? null : $price, $values['root']['monthly_rent']);
        }
    }

    /**
     * Vice domenovych chyb zachova vlastni nazvy poli
     */
    public function testDomainErrorsAreCollectedByField(): void
    {
        try {
            RentalData::fromEditor(['monthly_rent' => '-1', 'available_from' => 'invalid', 'external_url' => 'javascript:alert(1)', 'offer_state' => 'unknown']);
            self::fail('Invalid domain accepted');
        } catch (\Lemonade\Cms\Content\CmsContentValidationException $exception) {
            foreach (['monthly_rent', 'available_from', 'external_url', 'offer_state'] as $field) {
                self::assertArrayHasKey($field, $exception->errors());
            }
        }
        try {
            RentalData::fromEditor(['location' => ['nested'], 'external_url' => new \stdClass()]);
            self::fail('Structured domain accepted');
        } catch (\Lemonade\Cms\Content\CmsContentValidationException $exception) {
            self::assertSame(['external_url', 'location'], array_keys($exception->errors()));
        }
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
        yield 'too long' => [['monthly_rent' => str_repeat('x', 151)], 'rental.validation.price_invalid'];
        yield 'negative' => [['monthly_rent' => '-1'], 'rental.validation.price_invalid'];
        yield 'control character' => [['monthly_rent' => "Cena\nnově"], 'rental.validation.price_invalid'];
        yield 'date range' => [['available_from' => '0000-01-01'], 'rental.validation.date_invalid'];
        yield 'date' => [['available_from' => '2026-02-30'], 'rental.validation.date_invalid'];
        yield 'state' => [['offer_state' => 'published'], 'rental.validation.state_invalid'];
        yield 'url' => [['external_url' => 'javascript:alert(1)'], 'rental.validation.url_invalid'];
        yield 'location' => [['location' => str_repeat('x', 256)], 'rental.validation.location_invalid'];
    }
}

<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental\Migrations;

use Lemonade\Cms\Content\CmsContentSchema;
use Lemonade\Framework\Database\Migration\MigrationInterface;
use Lemonade\Framework\Database\Schema\Blueprint\TableBlueprint;
use Lemonade\Framework\Database\Schema\Schema;

/**
 * Vytvari oddelene tabulky pronajmu a jejich lokalizovanych mutaci
 */
final readonly class CreateRentalArticles implements MigrationInterface
{
    /**
     * Vrati stabilni identifikator migrace pronajmu
     */
    public static function identifier(): string
    {
        return '20261005130000_create_cms_rentals';
    }

    /**
     * Vytvori spolecne CMS sloupce a domenove udaje pronajmu
     */
    public function up(Schema $schema): void
    {
        $schema->create('cms_rental', static function (TableBlueprint $table): void {
            CmsContentSchema::root($table, 'cms_rental');
            $table->decimal('monthly_rent', 15, 2)->nullable();
            $table->string('currency', 3)->default('CZK');
            $table->date('available_from')->nullable();
            $table->string('offer_state', 32)->default('preparing');
            $table->string('external_url', 2048)->nullable();
        }, ifNotExists: true);
        $schema->create('cms_rental_translation', static function (TableBlueprint $table): void {
            CmsContentSchema::translation($table, 'cms_rental', 'article_id');
            $table->string('location', 255)->nullable();
        }, ifNotExists: true);
        $schema->create('cms_rental_translation_tag', static function (TableBlueprint $table): void {
            CmsContentSchema::tags($table, 'cms_rental', 'cms_rental_translation');
        }, ifNotExists: true);
    }
}

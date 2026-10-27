<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental;

use Lemonade\Admin\Authorization\AuthorizationService;
use Lemonade\Admin\Cms\Content\CmsAdminRuntime;
use Lemonade\Admin\Cms\Content\CmsContentAdminProvider;
use Lemonade\Admin\Identity\LocalActorGuard;
use Lemonade\Admin\Localization\LanguageRegistry;
use Lemonade\Admin\Module\AdminModuleRouteResolver;
use Lemonade\Cms\Content\CmsContentDefinition;
use Lemonade\Cms\Content\CmsContentLifecycle;
use Lemonade\Cms\Content\CmsDomainMapping;
use Lemonade\Cms\Content\Models\CmsContentModel;
use Lemonade\Cms\Rental\DataGrid\RentalDataGrid;
use Lemonade\Cms\Rental\Editor\RentalEditor;
use Lemonade\Cms\Rental\Editor\RentalEditorValidationSchema;
use Lemonade\Cms\Routing\Cms\CmsRouteReservationService;
use Lemonade\Framework\Container\ContainerBuilderInterface;
use Lemonade\Framework\Container\ContainerInterface;
use Lemonade\Framework\Core\BootableServiceProviderInterface;
use Lemonade\Framework\Core\ServiceProviderInterface;
use Lemonade\Framework\Database\Database;
use Lemonade\Framework\Routing\UrlGenerator;
use Lemonade\Framework\Support\Slug\Slugger;

/**
 * Pripojuje modul k existujicimu sdilenemu CMS Admin transportu
 */
final readonly class CmsRentalServiceProvider implements ServiceProviderInterface, BootableServiceProviderInterface
{
    /**
     * Registruje editorove action transporty modulu
     */
    public function register(ContainerBuilderInterface $container): void
    {
        $container->scoped('cms.rental.definition', static fn(): CmsContentDefinition => self::contentDefinition());
        $container->scoped('cms.rental.content_model', static fn(ContainerInterface $container): CmsContentModel => new CmsContentModel(
            $container->get(Database::class),
            $container->get('cms.rental.definition'),
        ));
        $container->scoped('cms.rental.lifecycle', static fn(ContainerInterface $container): CmsContentLifecycle => new CmsContentLifecycle(
            $container->get('cms.rental.definition'),
            $container->get('cms.rental.content_model'),
            new CmsDomainMapping(RentalData::class),
            $container->get(CmsRouteReservationService::class),
            $container->get(Slugger::class),
            $container->get(CmsAdminRuntime::class),
        ));
        $container->scoped(RentalEditor::class, static fn(ContainerInterface $container): RentalEditor => new RentalEditor(
            $container->get('cms.rental.read'),
            $container->get('cms.rental.lifecycle'),
            $container->get(LanguageRegistry::class),
            $container->get(LocalActorGuard::class),
            $container->get(RentalEditorValidationSchema::class),
            $container->get(AdminModuleRouteResolver::class),
            $container->get(UrlGenerator::class),
            $container->get(AuthorizationService::class),
        ));
        self::adminProvider()->register($container);
    }

    /**
     * Vrati registrovane tabulky a atributove mapovani obsahu
     */
    public static function contentDefinition(): CmsContentDefinition
    {
        return new CmsContentDefinition(
            moduleCode: ModuleManifest::CODE,
            entityType: 'cms_rental',
            rootTable: 'cms_rental',
            translationTable: 'cms_rental_translation',
            translationForeignKey: 'article_id',
            tagTable: 'cms_rental_translation_tag',
            translationUniqueConstraint: 'uq_cms_rental_translation_locale',
            validationPrefix: 'rental',
            dataClass: RentalData::class,
        );
    }

    /**
     * Pripoji preklady, grid, editor, stranky a akce modulu
     */
    public function boot(ContainerInterface $container): void
    {
        self::adminProvider()->boot($container);
    }

    /**
     * Nastavi stavajici presentation a action providery modulu
     */
    public static function adminProvider(): CmsContentAdminProvider
    {
        return new CmsContentAdminProvider(
            moduleCode: ModuleManifest::CODE,
            translationGroup: 'rental',
            translationPath: __DIR__ . '/Resources/lang',
            dataGridService: RentalDataGrid::class,
            pageService: RentalModulePageProvider::class,
            editorService: RentalEditor::class,
            lifecycleService: 'cms.rental.lifecycle',
            contentDefinitionService: 'cms.rental.definition',
        );
    }
}

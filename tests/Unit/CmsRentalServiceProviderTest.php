<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental\Tests\Unit;

use Lemonade\Admin\Action\ModuleActionRegistry;
use Lemonade\Admin\DataGrid\DataGridRegistry;
use Lemonade\Admin\Editor\EditorRegistry;
use Lemonade\Admin\Localization\ClientTranslationGroupRegistry;
use Lemonade\Admin\Page\ModulePageRegistry;
use Lemonade\Cms\Rental\CmsRentalServiceProvider;
use Lemonade\Framework\Container\Container;
use Lemonade\Framework\Container\ContainerInterface;
use Lemonade\Framework\Localization\TranslationResourceRegistry;
use Lemonade\Framework\Routing\RouteRegistrarInterface;
use PHPUnit\Framework\TestCase;

/**
 * Overuje oddeleni administracniho Rental modulu od verejneho routingu hosta
 */
final class CmsRentalServiceProviderTest extends TestCase
{
    /**
     * Ověří napojení stávajících registrů i oddělená oprávnění akcí
     */
    public function testBootRegistersExistingAdminCapabilities(): void
    {
        $grid = new DataGridRegistry();
        $editors = new EditorRegistry();
        $pages = new ModulePageRegistry();
        $actions = new ModuleActionRegistry();
        $groups = new ClientTranslationGroupRegistry();
        $resources = new TranslationResourceRegistry();
        $services = [
            DataGridRegistry::class => $grid,
            EditorRegistry::class => $editors,
            ModulePageRegistry::class => $pages,
            ModuleActionRegistry::class => $actions,
            ClientTranslationGroupRegistry::class => $groups,
            TranslationResourceRegistry::class => $resources,
        ];
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnCallback(static fn(string $id): object => $services[$id]);

        (new CmsRentalServiceProvider())->boot($container);

        self::assertSame(\Lemonade\Cms\Rental\DataGrid\RentalDataGrid::class, $grid->providerServiceId('cms.rental'));
        self::assertSame(\Lemonade\Cms\Rental\Editor\RentalEditor::class, $editors->providerServiceId('cms.rental'));
        self::assertSame(\Lemonade\Cms\Rental\RentalModulePageProvider::class, $pages->indexProviderServiceId('cms.rental'));
        self::assertSame(\Lemonade\Cms\Rental\RentalModulePageProvider::class, $pages->editorProviderServiceId('cms.rental'));
        self::assertTrue($groups->has('rental'));
        foreach (['create', 'save', 'change-url', 'delete', 'restore', 'assign-owner'] as $action) {
            self::assertTrue($actions->has('cms.rental', $action));
        }
        self::assertSame('cms.rental.delete_own', $actions->action('cms.rental', 'delete')['definition']->permission());
    }

    /**
     * Registruje pouze administracni sluzby bez verejnych rout
     */
    public function testItDoesNotActivatePublicRentalRouting(): void
    {
        $container = new Container();

        (new CmsRentalServiceProvider())->register($container);

        self::assertTrue($container->isBound('admin.cms.rental.action.create'));
        self::assertSame([], iterator_to_array($container->tagged(RouteRegistrarInterface::class)));
    }
}

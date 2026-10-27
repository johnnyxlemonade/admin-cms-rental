<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental;

use Lemonade\Admin\Audit\AuditEventDefinition;
use Lemonade\Admin\Authorization\PermissionDefinition;
use Lemonade\Admin\Icon\AdminIcon;
use Lemonade\Admin\Module\AdminModuleMetadata;
use Lemonade\Admin\Module\AdminNavigationGroup;
use Lemonade\Admin\Module\Contract\AdminModuleDefinitionInterface;
use Lemonade\Admin\Module\Contract\AdminModuleFileCapabilitiesInterface;
use Lemonade\Admin\Modules\Manifest\ModuleManifestInterface;
use Lemonade\Admin\Modules\Migration\ModuleMigrationManifestInterface;
use Lemonade\Admin\Presentation\AdminFileCapabilities;
use Lemonade\Admin\Presentation\AdminFilePresentation;
use Lemonade\Framework\Database\Migration\MigrationInterface;

/**
 * Deklaruje optional CMS Rental modul a jeho schema
 */
final readonly class ModuleManifest implements
    ModuleManifestInterface,
    ModuleMigrationManifestInterface,
    AdminModuleDefinitionInterface,
    AdminModuleFileCapabilitiesInterface
{
    public const string CODE = 'cms.rental';

    /**
     * Vrati stabilni kod Rental modulu
     */
    public function code(): string
    {
        return self::CODE;
    }

    /**
     * Vrati prekladovy klic nazvu modulu
     */
    public function labelKey(): string
    {
        return 'rental.module.name';
    }

    /**
     * Vrati provider runtime contributions modulu
     */
    public function runtimeProvider(): string
    {
        return CmsRentalServiceProvider::class;
    }

    /**
     * Vrati migrace aplikovane standardnim migration lifecycle
     *
     * @return list<class-string<MigrationInterface>>
     */
    public function migrations(): array
    {
        return [Migrations\CreateRentalArticles::class];
    }

    /**
     * Vrati metadata generic Admin module transportu
     */
    public function adminMetadata(): AdminModuleMetadata
    {
        return new AdminModuleMetadata(
            nameKey: 'rental.module.name',
            icon: AdminIcon::Building,
            navigationGroup: AdminNavigationGroup::Content,
            navigationOrder: 10,
            routeSegment: 'rental',
            navigationPermission: 'cms.rental.view_own',
            descriptionKey: 'rental.module.description',
        );
    }

    /**
     * Vraci file capability Rental modulu
     */
    public function fileCapabilities(): AdminFileCapabilities
    {
        return new AdminFileCapabilities(
            thumbnail: AdminFilePresentation::Landscape,
            gallery: true,
            attachments: true,
            richTextImages: true,
            richTextFiles: true,
        );
    }

    /**
     * Vrati prava vyzadovana pro skutecne Rental operace
     *
     * @return list<PermissionDefinition>
     */
    public function permissionDefinitions(): array
    {
        return [
            new PermissionDefinition('cms.rental.view_own', self::CODE, 'rental.permissions.view_own'),
            new PermissionDefinition('cms.rental.view', self::CODE, 'rental.permissions.view', requires: ['cms.rental.view_own']),
            new PermissionDefinition('cms.rental.create', self::CODE, 'rental.permissions.create', requires: ['cms.rental.view_own']),
            new PermissionDefinition('cms.rental.edit_own', self::CODE, 'rental.permissions.edit_own', requires: ['cms.rental.view_own']),
            new PermissionDefinition('cms.rental.edit', self::CODE, 'rental.permissions.edit', requires: ['cms.rental.edit_own', 'cms.rental.view']),
            new PermissionDefinition('cms.rental.publish_own', self::CODE, 'rental.permissions.publish_own', requires: ['cms.rental.view_own']),
            new PermissionDefinition('cms.rental.publish', self::CODE, 'rental.permissions.publish', requires: ['cms.rental.publish_own', 'cms.rental.view']),
            new PermissionDefinition('cms.rental.delete_own', self::CODE, 'rental.permissions.delete_own', requires: ['cms.rental.view_own']),
            new PermissionDefinition('cms.rental.delete', self::CODE, 'rental.permissions.delete', requires: ['cms.rental.delete_own', 'cms.rental.view']),
            new PermissionDefinition('cms.rental.restore_own', self::CODE, 'rental.permissions.restore_own', requires: ['cms.rental.view_own']),
            new PermissionDefinition('cms.rental.restore', self::CODE, 'rental.permissions.restore', requires: ['cms.rental.restore_own', 'cms.rental.view']),
            new PermissionDefinition('cms.rental.assign_owner', self::CODE, 'rental.permissions.assign_owner', requires: ['cms.rental.view_own']),
        ];
    }

    /**
     * Deklaruje presentation udalosti skutecnych mutaci obsahu Pronájmů
     *
     * @return list<AuditEventDefinition>
     */
    public function auditEventDefinitions(): array
    {
        return [
            new AuditEventDefinition('created', 'rental.audit.events.created', AdminIcon::PlusLg),
            new AuditEventDefinition('updated', 'rental.audit.events.updated', AdminIcon::PencilSquare),
            new AuditEventDefinition('translation_created', 'rental.audit.events.translation_created', AdminIcon::PlusLg),
            new AuditEventDefinition('translation_updated', 'rental.audit.events.translation_updated', AdminIcon::PencilSquare),
            new AuditEventDefinition('published', 'rental.audit.events.published', AdminIcon::CheckCircle),
            new AuditEventDefinition('unpublished', 'rental.audit.events.unpublished', AdminIcon::PauseCircle),
            new AuditEventDefinition('deleted', 'rental.audit.events.deleted', AdminIcon::Trash3),
            new AuditEventDefinition('restored', 'rental.audit.events.restored', AdminIcon::ArrowCounterclockwise),
            new AuditEventDefinition('owner_transferred', 'rental.audit.events.owner_transferred', AdminIcon::PersonGear),
        ];
    }
}

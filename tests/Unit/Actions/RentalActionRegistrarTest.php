<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental\Tests\Unit\Actions;

use Lemonade\Admin\Action\ModuleActionRegistry;
use Lemonade\Cms\Rental\CmsRentalServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Overuje explicitni registraci Rental actions a jejich permission hranice
 */
final class RentalActionRegistrarTest extends TestCase
{
    /**
     * Registruje URL akci oddelene od standardniho editoroveho save
     */
    public function testRegistersIndependentUrlChangeAction(): void
    {
        $actions = new ModuleActionRegistry();
        CmsRentalServiceProvider::adminProvider()->registerActions($actions);

        self::assertTrue($actions->has('cms.rental', 'change-url'));
        self::assertSame(
            'cms.rental.edit_own',
            $actions->action('cms.rental', 'change-url')['definition']->permission(),
        );
        self::assertTrue($actions->has('cms.rental', 'assign-owner'));
    }
}

<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental\DataGrid;

use Lemonade\Admin\Action\Presentation\ModuleActionPresentationFactory;
use Lemonade\Admin\Authorization\AuthorizationService;
use Lemonade\Admin\Cms\Content\CmsContentDataGrid;
use Lemonade\Admin\Cms\Content\CmsDataGridColumn;
use Lemonade\Admin\Cms\Content\CmsDataGridDefinition;
use Lemonade\Admin\Cms\Content\CmsDataGridFilter;
use Lemonade\Admin\DataGrid\Action\DataGridRowActionDefinition;
use Lemonade\Admin\DataGrid\Cell\ScalarCell;
use Lemonade\Admin\DataGrid\Cell\StackedCell;
use Lemonade\Admin\DataGrid\Cell\StatusCell;
use Lemonade\Admin\DataGrid\Cell\StatusVariant;
use Lemonade\Admin\DataGrid\Cell\ThumbnailCell;
use Lemonade\Admin\DataGrid\Contract\DataGridProviderInterface;
use Lemonade\Admin\DataGrid\DataGridColumnDefinition;
use Lemonade\Admin\DataGrid\DataGridDefinition;
use Lemonade\Admin\DataGrid\DataGridFilterDefinition;
use Lemonade\Admin\DataGrid\DataGridResult;
use Lemonade\Admin\DataGrid\DataGridRowDefinition;
use Lemonade\Admin\DataGrid\Query\DataGridQuery;
use Lemonade\Admin\Icon\AdminIcon;
use Lemonade\Admin\Localization\LanguageRegistry;
use Lemonade\Admin\Module\AdminModuleRouteResolver;
use Lemonade\Admin\Presentation\AdminThumbnailComponent;
use Lemonade\Admin\Presentation\Models\AdminFileModel;
use Lemonade\Admin\Select\SelectOptionDefinition;
use Lemonade\Admin\Select\StaticSelectOptionSource;
use Lemonade\Cms\CmsPublicationState;
use Lemonade\Cms\Content\CmsContentReadModel;
use Lemonade\Framework\Localization\TranslatorInterface;
use Lemonade\Framework\Routing\UrlGenerator;

/**
 * Promita Rental clanky do shared Admin DataGridu bez translation N+1 dotazu
 */
final readonly class RentalDataGrid implements DataGridProviderInterface
{
    /**
     * Nastavuje Rental persistence projekci pro DataGrid
     */
    public function __construct(
        private CmsContentReadModel $rental,
        private AuthorizationService $authorization,
        private TranslatorInterface $translator,
        private ModuleActionPresentationFactory $actionPresentation,
        private AdminModuleRouteResolver $routes,
        private UrlGenerator $urls,
        private AdminFileModel $files,
        private AdminThumbnailComponent $thumbnails,
        private LanguageRegistry $languages,
        private CmsContentDataGrid $contentGrid,
    ) {}

    /**
     * Deklaruje pouze domenove rozsireni standardniho CMS dotazu
     */
    public static function queryDefinition(): CmsDataGridDefinition
    {
        return new CmsDataGridDefinition(
            columns: [
                new CmsDataGridColumn('location', searchable: true),
                new CmsDataGridColumn('monthlyRent'),
                new CmsDataGridColumn('currency', sortable: false),
                new CmsDataGridColumn('availableFrom'),
                new CmsDataGridColumn('offerState'),
                new CmsDataGridColumn('createdAt'),
            ],
            filters: [new CmsDataGridFilter('offerState', ['preparing', 'available', 'reserved', 'rented', 'cancelled'])],
        );
    }

    /**
     * Vrati pravo potrebne pro nacteni Rental prehledu
     */
    public function permission(): string
    {
        return 'cms.rental.view_own';
    }

    /**
     * Deklaruje sloupce, search, filtry a deterministic razeni prehledu
     */
    public function dataGridDefinition(): DataGridDefinition
    {
        return new DataGridDefinition(
            id: 'cms-rental',
            columns: [
                new DataGridColumnDefinition('title', 'rental.fields.title', 'title'),
                new DataGridColumnDefinition('location', 'rental.fields.location', 'location'),
                new DataGridColumnDefinition('monthlyRent', 'rental.fields.monthly_rent', 'monthlyRent'),
                new DataGridColumnDefinition('availableFrom', 'rental.fields.available_from', 'availableFrom'),
                new DataGridColumnDefinition('offerState', 'rental.fields.offer_state', 'offerState'),
                new DataGridColumnDefinition('state', 'rental.fields.publication_state', 'state'),
                new DataGridColumnDefinition('publishedAt', 'rental.fields.published_at', 'publishedAt', 'd-none d-lg-table-cell', configurable: true),
                new DataGridColumnDefinition('owner', 'rental.fields.owner', class: 'd-none d-xl-table-cell', configurable: true),
                new DataGridColumnDefinition('createdAt', 'rental.fields.created_at', 'createdAt', configurable: true),
                new DataGridColumnDefinition('updatedAt', 'rental.fields.updated_at', 'updatedAt', 'd-none d-xl-table-cell', configurable: true),
                new DataGridColumnDefinition('deletedAt', 'rental.fields.deleted_at', configurable: true, visibleByDefault: false),
                new DataGridColumnDefinition('actions', 'admin.common.actions'),
            ],
            searchEnabled: true,
            filters: [
                new DataGridFilterDefinition(
                    key: 'status',
                    optionSource: new StaticSelectOptionSource(
                        options: [
                            new SelectOptionDefinition('active', $this->translator->get('rental.list.active')),
                            new SelectOptionDefinition('deleted', $this->translator->get('rental.list.deleted')),
                        ],
                    ),
                ),
                new DataGridFilterDefinition('state', new StaticSelectOptionSource([
                    new SelectOptionDefinition('new', $this->translator->get('admin.status.new')),
                    new SelectOptionDefinition('draft', $this->translator->get('admin.status.draft')),
                    new SelectOptionDefinition('published', $this->translator->get('admin.status.published')),
                ])),
                new DataGridFilterDefinition('offerState', new StaticSelectOptionSource(array_map(
                    fn(string $state): SelectOptionDefinition => new SelectOptionDefinition($state, $this->translator->get('rental.states.' . $state)),
                    ['preparing', 'available', 'reserved', 'rented', 'cancelled'],
                ))),
                new DataGridFilterDefinition('locale', new StaticSelectOptionSource(array_map(
                    fn(array $language): SelectOptionDefinition => new SelectOptionDefinition($language['code'], $language['name']),
                    $this->rental->dataGridLocales(),
                ))),
            ],
            defaultSortKey: 'publishedAt',
            defaultSortDirection: 'desc',
            defaultPageSize: 25,
            maximumPageSize: 100,
            defaultView: 'active',
            showAllView: false,
        );
    }

    /**
     * Vrati typed radky z jedine batch projekce Rental modelu
     */
    public function execute(DataGridQuery $query): DataGridResult
    {
        $canViewAll = $this->authorization->hasPermission('cms.rental.view');
        $ownerId = $canViewAll ? null : $this->authorization->currentUserId();
        if (!$canViewAll && $ownerId === null) {
            return new DataGridResult([], $query->page(), $query->pageSize(), 0);
        }
        $page = $this->contentGrid->listForDataGrid(
            $query,
            $this->languages->defaultLocale(),
            $ownerId,
            self::queryDefinition(),
        );
        $featuredImages = $this->files->findForEntities(
            'cms.rental',
            array_map(static fn(array $article): int => (int) $article['id'], $page->items()),
            'thumbnail',
        );
        $rows = array_map(
            fn(array $article): DataGridRowDefinition => $this->row(
                $article,
                $featuredImages[(int) $article['id']] ?? null,
            ),
            $page->items(),
        );

        return new DataGridResult($rows, $page->page(), $page->perPage(), $page->total());
    }

    /**
     * Promita jeden clanek na odkazovatelny aktivni nebo read-only smazany radek
     *
     * @param array<string,mixed> $article
     * @param array{
     *     id:int,
     *     module_code:string,
     *     entity_id:int,
     *     usage:string,
     *     kind:string,
     *     original_filename:string,
     *     display_name:string|null,
     *     caption:string|null,
     *     extension:string,
     *     mime_type:string,
     *     file_size:int,
     *     width:int|null,
     *     height:int|null,
     * }|null $featuredImage
     */
    private function row(array $article, ?array $featuredImage): DataGridRowDefinition
    {
        $articleId = (int) $article['id'];
        $locale = (string) $article['locale'];
        $title = trim((string) ($article['title'] ?? ''));
        $displayTitle = $title === ''
            ? $this->translator->get('admin.datagrid.untitled')
            : $title;
        $deleted = $article['deleted_at'] !== null;
        $ownerId = $article['owner_id'] === null ? null : (int) $article['owner_id'];
        $editable = !$deleted && $this->authorization->canForOwner(
            'cms.rental.edit',
            'cms.rental.edit_own',
            $ownerId,
        );

        return new DataGridRowDefinition(
            id: $articleId,
            cells: [
                'title' => new ThumbnailCell(
                    value: $displayTitle,
                    thumbnail: $featuredImage === null
                        ? $this->thumbnails->fallback(
                            fallback: null,
                            size: 'compact',
                            shape: 'rounded',
                        )
                        : $this->thumbnails->image(
                            module: 'cms.rental',
                            imageId: (string) $featuredImage['id'],
                            alt: $displayTitle,
                            fallback: null,
                            size: 'compact',
                            shape: 'rounded',
                            presentation: 'datagrid',
                        ),
                    secondary: (string) ($article['canonical_path'] ?? ''),
                    url: $editable ? $this->editUrl($articleId, $locale) : null,
                ),
                'location' => new ScalarCell((string) ($article['location'] ?? '—')),
                'monthlyRent' => new ScalarCell($article['monthly_rent'] === null ? '—' : (string) $article['monthly_rent'] . ' CZK'),
                'availableFrom' => new ScalarCell((string) ($article['available_from'] ?? '—')),
                'offerState' => new ScalarCell($this->translator->get('rental.states.' . $article['offer_state'])),
                'state' => new StatusCell(
                    $this->publicationStateLabel((int) $article['publication_state']),
                    (int) $article['publication_state'] === CmsPublicationState::Published->value ? StatusVariant::Success : StatusVariant::Muted,
                ),
                'publishedAt' => new ScalarCell((string) ($article['published_at'] ?? '—')),
                'owner' => new ScalarCell((string) ($article['owner_name'] ?? '—')),
                'createdAt' => $this->auditMetadataCell($article['created_at'] ?? null, $article['created_by_name'] ?? null),
                'updatedAt' => $this->auditMetadataCell($article['updated_at'] ?? null, $article['updated_by_name'] ?? null),
                'deletedAt' => $this->auditMetadataCell($article['deleted_at'] ?? null, $article['deleted_by_name'] ?? null),
            ],
            actions: $this->rowActions($articleId, $deleted, $locale, $ownerId),
        );
    }

    /**
     * Vrati lokalizovany label publication stavu z databazove hodnoty
     */
    private function publicationStateLabel(int $value): string
    {
        $state = CmsPublicationState::tryFrom($value) ?? CmsPublicationState::New;

        return $this->translator->get('admin.status.' . match ($state) {
            CmsPublicationState::New => 'new',
            CmsPublicationState::Draft => 'draft',
            CmsPublicationState::Published => 'published',
        });
    }

    /**
     * Nabizi editaci pouze jako neautoritativni presentation akci
     *
     * @return list<DataGridRowActionDefinition>
     */
    private function rowActions(
        int $articleId,
        bool $deleted,
        string $contentLocale,
        ?int $ownerId,
    ): array {
        $actions = [];
        if (!$deleted && $this->authorization->canForOwner('cms.rental.edit', 'cms.rental.edit_own', $ownerId)) {
            $actions[] = (new DataGridRowActionDefinition(
                key: 'edit',
                label: $this->translator->get('admin.common.edit'),
                url: $this->editUrl($articleId, $contentLocale),
                method: 'GET',
            ))->withIcon(AdminIcon::PencilSquare);
        }
        $canChangeLifecycle = $deleted
            ? $this->authorization->canForOwner('cms.rental.restore', 'cms.rental.restore_own', $ownerId)
            : $this->authorization->canForOwner('cms.rental.delete', 'cms.rental.delete_own', $ownerId);
        if ($canChangeLifecycle) {
            $action = $this->actionPresentation->rowAction(
                moduleCode: 'cms.rental',
                action: $deleted ? 'restore' : 'delete',
                entityId: $articleId,
            );
            if ($action !== null) {
                $actions[] = $action;
            }
        }

        return $actions;
    }

    /**
     * Zobrazi auditni datum nad identitou uzivatele
     */
    private function auditMetadataCell(mixed $date, mixed $actor): StackedCell
    {
        $date = trim((string) $date);
        $actor = trim((string) $actor);

        return new StackedCell(
            value: $date === '' ? '—' : $date,
            secondary: $date === '' || $actor === '' ? '—' : $actor,
        );
    }

    /**
     * Sklada canonical URL editoru s explicitni content locale mutace
     */
    private function editUrl(int $articleId, ?string $contentLocale = null): string
    {
        $url = $this->urls->route(
            $this->routes->managementRouteName('rental', 'edit'),
            ['module' => 'rental', 'id' => $articleId],
        );

        return $contentLocale === null ? $url : $url . '?locale=' . rawurlencode($contentLocale);
    }
}

<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental\Editor;

use Lemonade\Admin\Action\Presentation\AdminActionFormDefinition;
use Lemonade\Admin\Cms\Content\CmsDisplaySettings;
use Lemonade\Admin\Editor\AdminEditor\AdminEditorBuilder;
use Lemonade\Admin\Editor\AdminEditor\AdminEditorDefinition;
use Lemonade\Admin\Editor\AdminEditor\Field\Field;
use Lemonade\Admin\Editor\AdminEditor\Header\AdminEditorHeaderContextDefinition;
use Lemonade\Admin\Editor\AdminEditor\Layout\AdminEditorSidebarDefinition;
use Lemonade\Admin\Editor\AdminEditor\Layout\AdminEditorTab;
use Lemonade\Admin\Editor\AdminEditor\Layout\CmsEditorHeader;
use Lemonade\Admin\Editor\AdminEditor\Layout\CmsEditorSaveBar;
use Lemonade\Admin\Editor\AdminEditor\Layout\CmsMediaSection;
use Lemonade\Admin\Editor\AdminEditor\Layout\CmsOwnershipSection;
use Lemonade\Admin\Editor\AdminEditor\Layout\CmsPublicationSection;
use Lemonade\Admin\Editor\AdminEditor\Layout\CmsTagsField;
use Lemonade\Admin\Editor\AdminEditor\Layout\FieldColumn;
use Lemonade\Admin\Editor\AdminEditor\Layout\FieldGroupBlock;
use Lemonade\Admin\Editor\AdminEditor\Layout\SectionBlock;
use Lemonade\Admin\Editor\AdminEditor\Layout\SeoSection;
use Lemonade\Admin\Module\AdminModuleRouteResolver;
use Lemonade\Admin\Presentation\AdminFileUploadCollection;
use Lemonade\Framework\Routing\UrlGenerator;

/**
 * Builds the Rental editor from shared tabs, fields and save controls
 */
final readonly class RentalAdminEditorDefinitionFactory
{
    /**
     * Receives canonical Admin URL and module route services
     */
    public function __construct(
        private UrlGenerator $urls,
        private AdminModuleRouteResolver $routes,
    ) {}

    /**
     * Builds an editor for one content locale and its authorized controls
     *
     * @param array{id:int,translations:list<array<string,mixed>>} $article
     * @param list<string> $tagOptions
     * @param array<int,string> $ownerOptions
     */
    public function create(
        array $article,
        string $contentLocale,
        bool $canPublish,
        bool $canAssignOwner = false,
        array $ownerOptions = [],
        ?AdminEditorHeaderContextDefinition $context = null,
        ?AdminFileUploadCollection $featuredImageUpload = null,
        ?AdminFileUploadCollection $galleryUpload = null,
        ?AdminFileUploadCollection $attachmentUpload = null,
        array $tagOptions = [],
    ): AdminEditorDefinition {
        $articleId = $article['id'];
        $translation = $article['translations'][0] ?? [];
        $indexUrl = $this->urls->route($this->routes->managementRouteName('rental', 'index'), ['module' => 'rental']);
        $builder = AdminEditorBuilder::create('cms.rental')
            ->form($this->editorForm((int) $articleId, $contentLocale))
            ->header(CmsEditorHeader::create('rental', $indexUrl, $context))
            ->saveBar(CmsEditorSaveBar::create());
        $builder
            ->tab($this->contentTab($translation, $contentLocale, $tagOptions, $canPublish))
            ->tab(SeoSection::tab(pathPrefix: '/', autoGenerateUrl: trim((string) ($translation['canonical_path'] ?? '')) === ''))
            ->tab($this->settingsTab())
            ->tab(CmsMediaSection::gallery('rental', $galleryUpload))
            ->tab(CmsMediaSection::attachments('rental', $attachmentUpload));

        $sidebarSections = [];
        $publication = CmsPublicationSection::create('rental', $canPublish);
        if ($publication !== null) {
            $sidebarSections[] = $publication;
        }
        $sidebarSections[] = CmsOwnershipSection::create('rental', $article, $canAssignOwner, $ownerOptions);
        $thumbnail = CmsMediaSection::thumbnail('rental', $featuredImageUpload);
        if ($thumbnail !== null) {
            $sidebarSections[] = $thumbnail;
        }
        $builder->sidebar(new AdminEditorSidebarDefinition($sidebarSections));

        return $builder->build();
    }

    /**
     * Builds the action form used to save an existing article
     */
    private function editorForm(int $articleId, string $contentLocale): AdminActionFormDefinition
    {
        $routeParams = ['module' => 'rental', 'id' => $articleId, 'locale' => $contentLocale];

        return new AdminActionFormDefinition(
            id: 'rental-editor-form',
            action: $this->urls->route(name: $this->routes->managementRouteName('rental', 'update'), params: $routeParams),
            actionUrl: $this->urls->route(name: $this->routes->managementRouteName('rental', 'ajax.entity'), params: $routeParams),
            actionKey: 'save',
            novalidate: true,
        );
    }

    /**
     * Builds the localized content and rich-text editor tab
     *
     * @param array<string,mixed> $translation
     * @param list<string> $tagOptions
     */
    private function contentTab(array $translation, string $contentLocale, array $tagOptions, bool $canPublish): AdminEditorTab
    {
        return new AdminEditorTab(
            id: 'content',
            label: '',
            blocks: [
                new SectionBlock(
                    id: 'content-basic',
                    blocks: [
                        Field::hidden('locale')->defaultValue($contentLocale),
                        new FieldGroupBlock([
                            new FieldColumn(
                                Field::text('title', labelKey: 'rental.fields.title')
                                    ->required()
                                    ->attributes(['maxlength' => '180'] + CmsPublicationSection::requiredAttributes((int) ($translation['publication_state'] ?? 0), $canPublish)),
                                md: 12,
                            ),
                            new FieldColumn(
                                Field::text('author_name', labelKey: 'rental.fields.author_name')
                                    ->attributes(['maxlength' => '255']),
                                md: 12,
                            ),
                            new FieldColumn(
                                Field::text('location', labelKey: 'rental.fields.location')
                                    ->help('', 'rental.fields.location_help')
                                    ->attributes(['maxlength' => '255']),
                                md: 6,
                            ),
                            new FieldColumn(
                                Field::text('external_url', labelKey: 'rental.fields.external_url')
                                    ->help('', 'rental.fields.external_url_help')
                                    ->attributes(['maxlength' => '2048']),
                                md: 6,
                            ),
                            new FieldColumn(
                                Field::text('monthly_rent', labelKey: 'rental.fields.monthly_rent')
                                    ->help('', 'rental.fields.monthly_rent_help')
                                    ->attributes(['maxlength' => '150']),
                                md: 6,
                                lg: 4,
                            ),
                            new FieldColumn(
                                Field::date('available_from', labelKey: 'rental.fields.available_from')
                                    ->help('', 'rental.fields.available_from_help'),
                                md: 6,
                                lg: 4,
                            ),
                            new FieldColumn(
                                Field::select('offer_state', labelKey: 'rental.fields.offer_state')
                                    ->optionLabelKeys([
                                        'preparing' => 'rental.states.preparing',
                                        'available' => 'rental.states.available',
                                        'reserved' => 'rental.states.reserved',
                                        'rented' => 'rental.states.rented',
                                        'cancelled' => 'rental.states.cancelled',
                                    ])
                                    ->options(['preparing' => '', 'available' => '', 'reserved' => '', 'rented' => '', 'cancelled' => ''])
                                    ->defaultValue('preparing')
                                    ->help('', 'rental.fields.offer_state_help'),
                                md: 6,
                                lg: 4,
                            ),
                            CmsTagsField::column('rental', $tagOptions, $translation['tags'] ?? []),
                            new FieldColumn(
                                Field::textarea('summary', labelKey: 'rental.fields.summary')
                                    ->help('', 'rental.fields.summary_help'),
                                md: 12,
                            ),
                        ]),
                    ],
                    title: '',
                    titleKey: 'rental.editor.sections.localized_content',
                    descriptionKey: 'rental.editor.sections.localized_content_help',
                ),
                new SectionBlock(
                    id: 'content-body',
                    blocks: [
                        new FieldGroupBlock([
                            new FieldColumn(
                                Field::textarea('content', labelKey: 'rental.fields.content')
                                    ->richText(imageUpload: true, fileUpload: true),
                                md: 12,
                            ),
                        ]),
                    ],
                    title: '',
                    titleKey: 'rental.editor.sections.content',
                    descriptionKey: 'rental.editor.sections.content_help',
                ),
            ],
            labelKey: 'rental.editor.tabs.content',
            default: true,
        );
    }

    /**
     * Builds root display settings for rental presentation
     */
    private function settingsTab(): AdminEditorTab
    {
        return new AdminEditorTab(
            id: 'settings',
            label: '',
            blocks: [
                ...CmsDisplaySettings::tab()->blocks(),
            ],
            labelKey: 'rental.editor.tabs.settings',
        );
    }
}

<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental\Editor;

use DomainException;
use Lemonade\Admin\Authorization\AuthorizationService;
use Lemonade\Admin\Editor\Contract\EditorAccessPolicyInterface;
use Lemonade\Admin\Editor\Contract\EditorFieldAccessPolicyInterface;
use Lemonade\Admin\Editor\Contract\EditorProviderInterface;
use Lemonade\Admin\Editor\EditorDefinition;
use Lemonade\Admin\Editor\EditorFieldDefinition;
use Lemonade\Admin\Editor\EditorSaveResult;
use Lemonade\Admin\Editor\Exception\EditorEntityNotFoundException;
use Lemonade\Admin\Editor\Exception\EditorValidationException;
use Lemonade\Admin\Identity\LocalActorGuard;
use Lemonade\Admin\Localization\LanguageRegistry;
use Lemonade\Admin\Module\AdminModuleRouteResolver;
use Lemonade\Cms\Content\CmsContentInput;
use Lemonade\Cms\Content\CmsContentLifecycle;
use Lemonade\Cms\Content\CmsContentReadModel;
use Lemonade\Cms\Rental\RentalData;
use Lemonade\Framework\Routing\UrlGenerator;
use Lemonade\Framework\Validation\ValidationSchema;

/**
 * Propojuje Rental full-page editor s canonical lifecycle a mutacemi obsahu
 */
final readonly class RentalEditor implements
    EditorProviderInterface,
    EditorAccessPolicyInterface,
    EditorFieldAccessPolicyInterface
{
    /**
     * Nastavuje Rental persistence, mutation service, preklady a auditni identitu editoru
     *
     * @param CmsContentLifecycle<RentalData> $service
     */
    public function __construct(
        private CmsContentReadModel $rental,
        private CmsContentLifecycle $service,
        private LanguageRegistry $languages,
        private LocalActorGuard $actors,
        private RentalEditorValidationSchema $validation,
        private AdminModuleRouteResolver $routes,
        private UrlGenerator $urls,
        private AuthorizationService $authorization,
    ) {}

    /**
     * Deklaruje transportni pole editoru vcetne dynamic translations payloadu
     */
    public function editorDefinition(): EditorDefinition
    {
        return new EditorDefinition(
            loadPermission: 'cms.rental.view_own',
            savePermission: 'cms.rental.edit_own',
            createPermission: 'cms.rental.create',
            fields: [
                new EditorFieldDefinition(name: 'publication_state', type: 'text', readOnly: false, permission: 'cms.rental.publish_own'),
                new EditorFieldDefinition(name: 'published_at', type: 'text', readOnly: false, permission: 'cms.rental.publish_own'),
                new EditorFieldDefinition(name: 'author_name', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'location', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'monthly_rent', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'available_from', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'offer_state', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'external_url', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'locale', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'title', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'summary', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'content', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'page_title', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'meta_description', type: 'text', readOnly: false, permission: null),
                new EditorFieldDefinition(name: 'tags', type: 'text', readOnly: false, permission: null),
            ],
        );
    }

    /**
     * Overi globalni nebo vlastnicky pristup ke cteni clanku
     */
    public function loadAccessDecision(int $id): bool
    {
        return $this->authorization->canForOwner(
            'cms.rental.view',
            'cms.rental.view_own',
            $this->rental->ownerId($id),
        );
    }

    /**
     * Overi globalni nebo vlastnicky pristup k beznemu ulozeni clanku
     */
    public function saveAccessDecision(int $id): bool
    {
        return $this->authorization->canForOwner(
            'cms.rental.edit',
            'cms.rental.edit_own',
            $this->rental->ownerId($id),
        );
    }

    /**
     * Overi publish pole proti aktualnimu vlastnikovi root aggregate
     */
    public function fieldAccessDecision(int $id, string $field): ?bool
    {
        if (!in_array($field, ['publication_state', 'published_at'], true)) {
            return null;
        }

        return $this->authorization->canForOwner(
            'cms.rental.publish',
            'cms.rental.publish_own',
            $this->rental->ownerId($id),
        );
    }

    /**
     * Nacte existujici Rental aggregate pro editaci
     */
    public function updateData(int $id): array
    {
        $article = $this->rental->findForEditor($id);
        if ($article === null) {
            throw new EditorEntityNotFoundException('Rental article not found.');
        }

        return ['article' => $article, 'locales' => $this->rental->editorLocales($id)];
    }

    /**
     * Vrati prazdny editorovy stav pro nepouzitou fallback create route
     */
    public function createData(): array
    {
        $locales = $this->languages->enabledLocales();

        return [
            'article' => [
                'id' => null,
                'author_name' => null,
                'translations' => [],
            ],
            'locales' => $locales,
        ];
    }

    /**
     * Vrati prazdnou validaci pro okamzite zalozeni draftu bez vstupu uzivatele
     */
    public function createValidationSchema(): ValidationSchema
    {
        return ValidationSchema::create();
    }

    /**
     * Vrati validaci pro zmenu globalnich dat a aktualni content mutation
     */
    public function updateValidationSchema(int $id): ValidationSchema
    {
        return $this->validation->forUpdate();
    }

    /**
     * Ulozi predane neprazdne mutation a lifecycle clanku
     */
    public function update(int $id, array $data): EditorSaveResult
    {
        try {
            $actorId = $this->actors->requireLocalUser()->id();
            if (($data['publication_state'] ?? null) === null) {
                unset($data['publication_state'], $data['published_at']);
            }
            $this->service->save($actorId, $id, CmsContentInput::fromEditor($data), RentalData::fromEditor($data));
        } catch (DomainException $exception) {
            throw new EditorValidationException($exception->getMessage(), previous: $exception);
        }

        return new EditorSaveResult(
            data: ['id' => $id, 'editUrl' => $this->editUrl($id, (string) $data['locale'])],
            messageKey: 'rental.editor.updated',
        );
    }

    /**
     * Zalozi minimalni draft v canonical vychozim content locale
     */
    public function create(array $data): EditorSaveResult
    {
        try {
            $actorId = $this->actors->requireLocalUser()->id();
            $locale = $this->languages->defaultLocale();
            $articleId = $this->service->createDraft($actorId, $locale);
        } catch (DomainException $exception) {
            throw new EditorValidationException($exception->getMessage(), previous: $exception);
        }

        return new EditorSaveResult(
            data: [
                'id' => $articleId,
                'editUrl' => $this->editUrl($articleId, $locale),
            ],
            messageKey: 'rental.editor.created',
        );
    }

    /**
     * Vytvori canonical edit URL se zachovanym content locale
     */
    private function editUrl(int $articleId, string $locale): string
    {
        return $this->urls->route(
            $this->routes->managementRouteName('rental', 'edit'),
            ['module' => 'rental', 'id' => $articleId, 'locale' => $locale],
        );
    }
}

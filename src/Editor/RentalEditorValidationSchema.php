<?php

declare(strict_types=1);

namespace Lemonade\Cms\Rental\Editor;

use Lemonade\Admin\Cms\Content\CmsDisplaySettings;
use Lemonade\Cms\CmsPublicationState;
use Lemonade\Framework\Localization\TranslatorInterface;
use Lemonade\Framework\Validation\ValidationSchema;

/**
 * Sklada frameworkovou validaci create a update vstupu editoru Pronájmů
 */
final readonly class RentalEditorValidationSchema
{
    /**
     * Nastavuje prekladovy zdroj chyb editorove validace
     */
    public function __construct(private TranslatorInterface $translator) {}

    /**
     * Ponechava create bez formulovych hodnot pro okamzite zalozeni draftu
     */
    public function forCreate(): ValidationSchema
    {
        return ValidationSchema::create();
    }

    /**
     * Vymezuje flat payload jedne lokalizovane mutation a globalnich metadat
     */
    public function forUpdate(): ValidationSchema
    {
        return CmsDisplaySettings::validation(ValidationSchema::create()
            ->field('locale', $this->translator->get('rental.editor.content'))
                ->required($this->translator->get('rental.validation.locale_required'))
            ->field('title', $this->translator->get('rental.fields.title'))
                ->required($this->translator->get('rental.validation.title_required'))
                ->maxLength(180, $this->translator->get('rental.validation.title_max_length'))
            ->field('author_name', $this->translator->get('rental.fields.author_name'))
                ->maxLength(255, $this->translator->get('rental.validation.author_max_length'))
            ->field('page_title', $this->translator->get('rental.fields.page_title'))
                ->maxLength(180, $this->translator->get('rental.validation.page_title_max_length'))
            ->field('meta_description', $this->translator->get('rental.fields.meta_description'))
                ->maxLength(255, $this->translator->get('rental.validation.meta_description_max_length'))
            ->field('publication_state', $this->translator->get('rental.fields.publication_state'))
                ->inList(array_keys(CmsPublicationState::editorOptionLabelKeys()), $this->translator->get('rental.validation.publication_invalid'))
            ->field('published_at', $this->translator->get('rental.fields.published_at'))
            ->field('location', $this->translator->get('rental.fields.location'))
            ->field('monthly_rent', $this->translator->get('rental.fields.monthly_rent'))
            ->field('available_from', $this->translator->get('rental.fields.available_from'))
            ->field('offer_state', $this->translator->get('rental.fields.offer_state'))
            ->field('external_url', $this->translator->get('rental.fields.external_url'))
            ->field('summary', $this->translator->get('rental.fields.summary'))
            ->field('content', $this->translator->get('rental.fields.content'))
            ->field('tags', $this->translator->get('rental.fields.tags'))
            ->end(), $this->translator);
    }
}

<?php

declare(strict_types=1);

return [
    'module' => [
        'name' => 'Pronájmy',
        'description' => 'Správa nabídek pronájmu a jejich publikace.',
    ],

    'actions' => [
        'create' => 'Přidat nabídku pronájmu',
        'confirm_create' => 'Vytvořit',
        'edit' => 'Upravit',
        'delete' => 'Smazat',
        'restore' => 'Obnovit',
        'assign_owner' => 'Změnit vlastníka',
        'assign_owner_submit' => 'Změnit vlastníka',
        'owner_assigned' => 'Vlastník nabídky pronájmu byl změněn',
        'deleted' => 'Nabídka pronájmu byla smazána',
        'restored' => 'Nabídka pronájmu byla obnovena jako koncept',
    ],

    'states' => [
        'preparing' => 'Připravovaná',
        'available' => 'Dostupná',
        'reserved' => 'Rezervovaná',
        'rented' => 'Pronajatá',
        'cancelled' => 'Zrušená',
    ],

    'fields' => [
        'location' => 'Lokalita',
        'monthly_rent' => 'Měsíční nájemné (CZK)',
        'available_from' => 'K dispozici od',
        'offer_state' => 'Stav nabídky pronájmu',
        'external_url' => 'Odkaz na nabídku',
        'title' => 'Název nabídky',
        'author_name' => 'Autor',
        'tags' => 'Štítky',
        'tags_help' => 'Vyberte existující štítek nebo zadejte nový.',
        'summary' => 'Úvodní text',
        'content' => 'Obsah',
        'thumbnail' => 'Hlavní obrázek',
        'publication_state' => 'Stav publikace',
        'published_at' => 'Datum publikace',
        'created_by' => 'Vytvořil',
        'created_at' => 'Vytvořeno',
        'updated_by' => 'Upravil',
        'updated_at' => 'Upraveno',
        'deleted_by' => 'Smazal',
        'deleted_at' => 'Smazáno',
        'owner' => 'Vlastník',
    ],

    'list' => [
        'title' => 'Pronájmy',
        'description' => 'Publikované nabídky pronájmu a koncepty',
        'search' => 'Hledat v nabídkách pronájmu…',
        'loading' => 'Načítání nabídek pronájmu…',
        'empty' => 'Zatím žádné nabídky pronájmu',
        'active' => 'Přehled',
        'deleted' => 'Smazané',
        'untitled_locale' => 'Bez názvu ({locale})',
    ],

    'filters' => [
        'state' => 'Stav publikace',
        'all_states' => 'Všechny stavy publikace',
        'locale' => 'Jazyk',
        'all_locales' => 'Všechny jazyky',
    ],

    'editor' => [
        'title' => 'Upravit nabídku pronájmu',
        'create_title' => 'Nová nabídka pronájmu',
        'content' => 'Obsah',
        'publication' => 'Publikace',
        'ownership' => 'Autorství a vlastnictví',
        'locale' => [
            'label' => 'Jazykové verze',
            'existing_translation' => 'Překlad je k dispozici',
            'new_translation' => 'Překlad chybí',
            'disabled' => 'Jazyk není aktivní',
        ],
        'tabs' => [
            'content' => 'Obsah',
            'settings' => 'Nastavení',
        ],
        'sections' => [
            'localized_content' => 'Obsah',
            'localized_content_help' => 'Základní informace o nabídce',
            'content' => 'Popis nabídky',
            'content_help' => 'Popis nabídky pronájmu',
            'gallery_help' => 'Obrázky v galerii',
            'attachments_help' => 'Soubory ke stažení',
            'publication_help' => 'Stav a datum publikace',
            'ownership_help' => 'Historie změn a vlastník',
            'thumbnail_help' => 'Obrázek v přehledu a při sdílení',
        ],
        'not_implemented' => 'Zatím není k dispozici',
        'thumbnail' => [
            'title' => 'Hlavní obrázek',
            'help' => 'Obrázek se zobrazí v přehledu a při sdílení.',
        ],
        'created' => 'Nabídka pronájmu byla vytvořena',
        'updated' => 'Nabídka pronájmu byla uložena',
    ],

    'validation' => [
        'price_invalid' => 'Zadejte platné měsíční nájemné s nejvýše dvěma desetinnými místy',
        'state_invalid' => 'Vyberte platný stav nabídky pronájmu',
        'url_invalid' => 'Zadejte platný odkaz HTTP nebo HTTPS',
        'date_invalid' => 'Zadejte platné datum dostupnosti',
        'location_invalid' => 'Lokalita může mít nejvýše 255 znaků',
        'translation_required' => 'Zadejte titulek alespoň v jednom jazyce',
        'locale_required' => 'Vyberte jazyk',
        'title_required' => 'Zadejte titulek',
        'title_max_length' => 'Titulek může mít nejvýše 180 znaků',
        'author_max_length' => 'Jméno autora může mít nejvýše 255 znaků',
        'page_title_max_length' => 'Titulek stránky může mít nejvýše 180 znaků',
        'meta_description_max_length' => 'Popis pro vyhledávače může mít nejvýše 255 znaků',
        'tag_max_length' => 'Štítek může mít nejvýše 255 znaků',
        'locale_invalid' => 'Vybraný jazyk není aktivní',
        'publication_invalid' => 'Vyberte platný stav publikace',
        'owner_invalid' => 'Vybraný vlastník neexistuje nebo byl smazán',
        'not_found' => 'Nabídka pronájmu neexistuje',
        'save_failed' => 'Nabídku pronájmu se nepodařilo uložit',
    ],

    'confirm' => [
        'delete' => 'Opravdu chcete tuto nabídku pronájmu smazat?',
        'restore' => 'Opravdu chcete tuto nabídku pronájmu obnovit jako koncept?',
    ],

    'audit' => [
        'events' => [
            'created' => 'Vytvořena nabídka pronájmu',
            'updated' => 'Upravena nabídka pronájmu',
            'translation_created' => 'Přidána jazyková verze nabídky pronájmu',
            'translation_updated' => 'Upravena jazyková verze nabídky pronájmu',
            'published' => 'Publikována nabídka pronájmu',
            'unpublished' => 'Nabídka pronájmu převedena na koncept',
            'deleted' => 'Smazána nabídka pronájmu',
            'restored' => 'Obnovena nabídka pronájmu jako koncept',
            'owner_transferred' => 'Změněn vlastník nabídky pronájmu',
        ],
    ],

    'permissions' => [
        'view' => 'Zobrazit nabídky pronájmu',
        'view_own' => 'Zobrazit vlastní nabídky pronájmu',
        'create' => 'Vytvářet nabídky pronájmu',
        'edit' => 'Upravovat nabídky pronájmu',
        'edit_own' => 'Upravovat vlastní nabídky pronájmu',
        'publish' => 'Publikovat nabídky pronájmu',
        'publish_own' => 'Publikovat vlastní nabídky pronájmu',
        'delete' => 'Mazat nabídky pronájmu',
        'delete_own' => 'Mazat vlastní nabídky pronájmu',
        'restore' => 'Obnovovat nabídky pronájmu',
        'restore_own' => 'Obnovovat vlastní nabídky pronájmu',
        'assign_owner' => 'Měnit vlastníka nabídek pronájmu',
    ],
];

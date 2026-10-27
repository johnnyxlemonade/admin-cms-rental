<?php

declare(strict_types=1);

return [
    'module' => ['name' => 'Pronájmy', 'description' => 'Správa pronájmů a jejich publikace.'],
    'actions' => ['create' => 'Přidat pronájem', 'confirm_create' => 'Vytvořit', 'edit' => 'Upravit', 'delete' => 'Smazat', 'restore' => 'Obnovit', 'assign_owner' => 'Předat vlastnictví', 'assign_owner_submit' => 'Předat', 'owner_assigned' => 'Vlastnictví pronájmu bylo předáno', 'deleted' => 'Pronájem byl smazán', 'restored' => 'Pronájem byl obnoven jako koncept'],
    'states' => ['preparing' => 'Připravovaná', 'available' => 'Dostupná', 'reserved' => 'Rezervovaná', 'rented' => 'Pronajatá', 'cancelled' => 'Zrušená'],
    'fields' => ['location' => 'Lokalita', 'monthly_rent' => 'Měsíční nájemné (CZK)', 'available_from' => 'Dostupné od', 'offer_state' => 'Stav nabídky', 'external_url' => 'Externí odkaz', 'title' => 'Název pronájmu', 'author_name' => 'Autor', 'tags' => 'Štítky', 'tags_help' => 'Vyberte existující štítek nebo zadejte nový.', 'page_title' => 'Název na stránce', 'page_title_help' => 'Doporučená délka je 50–60 znaků. Tento název se zobrazí ve výsledcích vyhledávání.', 'summary' => 'Perex', 'content' => 'Obsah', 'meta_description' => 'Meta description', 'meta_description_help' => 'Doporučená délka je 120–160 znaků. Tento popis se zobrazí ve výsledcích vyhledávání.', 'thumbnail' => 'Hlavní obrázek', 'publication_state' => 'Stav publikace', 'published_at' => 'Publikováno od', 'created_by' => 'Vytvořil', 'created_at' => 'Vytvořeno', 'updated_by' => 'Naposledy upravil', 'updated_at' => 'Aktualizováno', 'deleted_by' => 'Smazal', 'deleted_at' => 'Smazáno', 'owner' => 'Vlastník'],
    'list' => ['title' => 'Pronájmy', 'description' => 'Správa publikovaných a rozpracovaných pronájmů', 'search' => 'Hledat v pronájmech…', 'loading' => 'Načítání pronájmů…', 'empty' => 'Zatím zde nejsou žádné pronájmy', 'active' => 'Aktivní', 'deleted' => 'Smazané', 'untitled_locale' => 'Bez názvu ({locale})'],
    'filters' => ['state' => 'Stav', 'all_states' => 'Všechny stavy', 'locale' => 'Jazyk', 'all_locales' => 'Všechny jazyky'],
    'editor' => [
        'title' => 'Upravit pronájem',
        'create_title' => 'Nový pronájem',
        'content' => 'Obsah',
        'publication' => 'Publikace',
        'ownership' => 'Autorství a vlastnictví',
        'locale' => ['label' => 'Jazykové verze', 'existing_translation' => 'Existující překlad', 'new_translation' => 'Nový překlad', 'disabled' => 'Neaktivní jazyk'],
        'tabs' => ['content' => 'Obsah', 'seo' => 'SEO', 'settings' => 'Nastavení'],
        'sections' => ['localized_content' => 'Obsah', 'localized_content_help' => 'Základní informace o pronájmu', 'content' => 'Obsah pronájmu', 'content_help' => 'Text a hlavní obsah pronájmu', 'seo' => 'SEO nastavení', 'seo_help' => 'Nastavení názvu, popisu a adresy pro vyhledávače', 'gallery_help' => 'Obrázky přiřazené k záznamu', 'attachments_help' => 'Soubory ke stažení a další dokumenty', 'publication_help' => 'Stav zveřejnění a datum publikace', 'ownership_help' => 'Historie záznamu a správa vlastníka', 'thumbnail_help' => 'Hlavní náhled pronájmu v přehledech a odkazech'],
        'not_implemented' => 'Zatím neimplementováno',
        'thumbnail' => ['title' => 'Náhledový obrázek', 'help' => 'Obrázek se použije jako hlavní náhled pronájmu.'],
        'created' => 'Pronájem byl vytvořen',
        'updated' => 'Pronájem byl uložen',
    ],
    'validation' => ['price_invalid' => 'Zadejte platnou cenu s nejvýše dvěma desetinnými místy', 'state_invalid' => 'Stav nabídky není platný', 'url_invalid' => 'Zadejte platný odkaz HTTP nebo HTTPS', 'date_invalid' => 'Datum dostupnosti není platné', 'location_invalid' => 'Lokalita může mít nejvýše 255 znaků','translation_required' => 'Vyplňte nadpis alespoň v jednom jazyce', 'locale_required' => 'Vyberte jazyk', 'title_required' => 'Nadpis je povinný', 'title_max_length' => 'Nadpis může mít nejvýše 180 znaků', 'author_max_length' => 'Autor může mít nejvýše 255 znaků', 'page_title_max_length' => 'Název na stránce může mít nejvýše 180 znaků', 'meta_description_max_length' => 'Meta description může mít nejvýše 255 znaků', 'tag_max_length' => 'Štítek může mít nejvýše 255 znaků', 'locale_invalid' => 'Zvolený jazyk není aktivní', 'publication_invalid' => 'Stav publikace není platný', 'owner_invalid' => 'Vybraný vlastník neexistuje nebo byl odstraněn', 'not_found' => 'Pronájem neexistuje', 'save_failed' => 'Pronájem se nepodařilo uložit'],
    'confirm' => ['delete' => 'Opravdu chcete tento pronájem smazat?', 'restore' => 'Opravdu chcete tento pronájem obnovit jako koncept?'],
    'audit' => ['events' => ['created' => 'Vytvořen pronájem', 'updated' => 'Upraven pronájem', 'translation_created' => 'Přidána jazyková mutace pronájmu', 'translation_updated' => 'Upravena jazyková mutace pronájmu', 'published' => 'Publikován pronájem', 'unpublished' => 'Zrušeno publikování pronájmu', 'deleted' => 'Smazán pronájem', 'restored' => 'Obnoven pronájem jako koncept', 'owner_transferred' => 'Předáno vlastnictví pronájmu']],
    'permissions' => [
        'view' => 'Zobrazit pronájmy',
        'view_own' => 'Zobrazit vlastní pronájmy',
        'create' => 'Vytvářet pronájmy',
        'edit' => 'Upravovat pronájmy',
        'edit_own' => 'Upravovat vlastní pronájmy',
        'publish' => 'Publikovat pronájmy',
        'publish_own' => 'Publikovat vlastní pronájmy',
        'delete' => 'Mazat pronájmy',
        'delete_own' => 'Mazat vlastní pronájmy',
        'restore' => 'Obnovovat pronájmy',
        'restore_own' => 'Obnovovat vlastní pronájmy',
        'assign_owner' => 'Předávat vlastnictví pronájmů',
    ],
];

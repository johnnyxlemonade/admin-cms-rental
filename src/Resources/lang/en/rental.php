<?php

declare(strict_types=1);

return [
    'module' => ['name' => 'Rentals', 'description' => 'Manage rental and publishing.'],
    'actions' => ['create' => 'Add rental', 'confirm_create' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete', 'restore' => 'Restore', 'assign_owner' => 'Transfer ownership', 'assign_owner_submit' => 'Transfer', 'owner_assigned' => 'Rental ownership transferred', 'deleted' => 'Rental item deleted', 'restored' => 'Rental item restored as draft'],
    'states' => ['preparing' => 'Preparing', 'available' => 'Available', 'reserved' => 'Reserved', 'rented' => 'Rented', 'cancelled' => 'Cancelled'],
    'fields' => ['location' => 'Location', 'monthly_rent' => 'Monthly rent (CZK)', 'available_from' => 'Available from', 'offer_state' => 'Offer state', 'external_url' => 'External URL', 'title' => 'Rental name', 'author_name' => 'Author', 'tags' => 'Tags', 'tags_help' => 'Select an existing tag or enter a new one.', 'page_title' => 'Page title', 'page_title_help' => 'Recommended length is 50–60 characters. This title appears in search results.', 'summary' => 'Summary', 'content' => 'Content', 'meta_description' => 'Meta description', 'meta_description_help' => 'Recommended length is 120–160 characters. This description appears in search results.', 'thumbnail' => 'Thumbnail', 'publication_state' => 'Publication state', 'published_at' => 'Published from', 'created_by' => 'Created by', 'created_at' => 'Created', 'updated_by' => 'Last updated by', 'updated_at' => 'Updated', 'deleted_by' => 'Deleted by', 'deleted_at' => 'Deleted', 'owner' => 'Owner'],
    'list' => ['title' => 'Rental', 'description' => 'Manage published and draft rental', 'search' => 'Search rental…', 'loading' => 'Loading rental…', 'empty' => 'There is no rental yet', 'active' => 'Active', 'deleted' => 'Deleted', 'untitled_locale' => 'Untitled ({locale})'],
    'filters' => ['state' => 'State', 'all_states' => 'All states', 'locale' => 'Language', 'all_locales' => 'All languages'],
    'editor' => [
        'title' => 'Edit rental',
        'create_title' => 'New rental',
        'content' => 'Content',
        'publication' => 'Publication',
        'ownership' => 'Authorship and ownership',
        'locale' => ['label' => 'Language versions', 'existing_translation' => 'Existing translation', 'new_translation' => 'New translation', 'disabled' => 'Disabled language'],
        'tabs' => ['content' => 'Content', 'seo' => 'SEO', 'settings' => 'Settings'],
        'sections' => ['localized_content' => 'Content', 'localized_content_help' => 'Basic information about the rental item', 'content' => 'Rental content', 'content_help' => 'Rental text and main content', 'seo' => 'SEO settings', 'seo_help' => 'Search engine title, description, and URL settings', 'gallery_help' => 'Images assigned to the record', 'attachments_help' => 'Downloadable files and other documents', 'publication_help' => 'Publication status and publishing date', 'ownership_help' => 'Record history and owner management', 'thumbnail_help' => 'Primary rental preview in listings and links'],
        'not_implemented' => 'Not implemented yet',
        'thumbnail' => ['title' => 'Preview image', 'help' => 'The image is used as the primary preview for this rental item.'],
        'created' => 'Rental item created',
        'updated' => 'Rental item saved',
    ],
    'validation' => ['price_invalid' => 'Enter a valid price with at most two decimal places', 'state_invalid' => 'Invalid rental state', 'url_invalid' => 'Enter a valid HTTP or HTTPS URL', 'date_invalid' => 'Invalid rental date', 'location_invalid' => 'Location may not exceed 255 characters','translation_required' => 'Enter a title in at least one language', 'locale_required' => 'Select a language', 'title_required' => 'Title is required', 'title_max_length' => 'Title may not exceed 180 characters', 'author_max_length' => 'Author may not exceed 255 characters', 'page_title_max_length' => 'Page title may not exceed 180 characters', 'meta_description_max_length' => 'Meta description may not exceed 255 characters', 'tag_max_length' => 'A tag may not exceed 255 characters', 'locale_invalid' => 'The selected language is not enabled', 'publication_invalid' => 'The publication state is invalid', 'owner_invalid' => 'The selected owner does not exist or has been removed', 'not_found' => 'The rental item does not exist', 'save_failed' => 'The rental item could not be saved'],
    'confirm' => ['delete' => 'Do you really want to delete this rental item?', 'restore' => 'Do you really want to restore this rental item as a draft?'],
    'audit' => ['events' => ['created' => 'Rental item created', 'updated' => 'Rental item updated', 'translation_created' => 'Rental language version added', 'translation_updated' => 'Rental language version updated', 'published' => 'Rental item published', 'unpublished' => 'Rental item unpublished', 'deleted' => 'Rental item deleted', 'restored' => 'Rental item restored as draft', 'owner_transferred' => 'Rental ownership transferred']],
    'permissions' => [
        'view' => 'View rental',
        'view_own' => 'View own rental',
        'create' => 'Create rental',
        'edit' => 'Edit rental',
        'edit_own' => 'Edit own rental',
        'publish' => 'Publish rental',
        'publish_own' => 'Publish own rental',
        'delete' => 'Delete rental',
        'delete_own' => 'Delete own rental',
        'restore' => 'Restore rental',
        'restore_own' => 'Restore own rental',
        'assign_owner' => 'Transfer rental ownership',
    ],
];

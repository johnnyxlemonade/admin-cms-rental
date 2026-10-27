# CMS Rental

Samostatný administrační modul pronájmů (`cms.rental`) používá společný `CmsContentLifecycle`, lokalizovaný obsah, SEO, štítky, média a audit. Composer jej registruje prostřednictvím `extra.lemonade.modules`. Oprávnění se nepřidělují rolím automaticky.

Pro lokální připojení do Portalu přidejte do jeho lokálního Composer manifestu path repository ukazující na `../admin-cms-rental` a závislost `johnnyxlemonade/admin-cms-rental: dev-main`; distribuční manifest a lock se nemění. Veřejné routy nejsou součástí modulu.

# Architecture: gsitemap

## Purpose
A PrestaShop module that generates XML sitemaps for SEO purposes. Supports scheduled cron-based regeneration and configurable inclusion of products, categories, CMS pages, and manufacturers.

## Directory Structure
```
gsitemap.php              # Main module class — registration, admin config, sitemap generation
controllers/
  front/cron.php          # Front controller for cron-triggered sitemap regeneration
  front/index.php         # Security index
index.php                 # Security guard
upgrade/                  # Database upgrade scripts (one file per version change)
views/
  templates/admin/        # Back-office configuration template
translations/             # i18n string files
```

## Key Design Decisions
- **Cron-driven generation** — the sitemap is not generated on every request; instead a cron URL is exposed via `controllers/front/cron.php` that must be called periodically.
- **Upgrade scripts** — schema/data migrations use PrestaShop's `upgrade/` convention, with one file per version bump.
- Follows PrestaShop module conventions for hook registration and admin configuration panels.

## Extension Points
- Additional sitemap sections (e.g., blog posts) can be added by extending the generation logic in `gsitemap.php`.
- Override admin templates in `views/templates/admin/` to customize the back-office UI.
- Add cron scheduling via external cron job or PrestaShop's built-in scheduler.

## Dependency Flow
```
PrestaShop Core
  └─ gsitemap (Module)
       ├─ Admin hook → configuration form → DB config storage
       ├─ Cron controller → generateSitemap() → XML file write
       └─ Sitemap entries: Products + Categories + CMS + Manufacturers
```

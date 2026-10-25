# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

**Primary — Farm Manager / Owner (Lewis):** Manages the chicken farm day-to-day. Tracks flocks, sales, feed, expenses, vaccination schedules, and production from an admin panel. Uses a desktop or mid-range Android phone. Needs a fast, no-nonsense tool that surfaces critical numbers quickly.

**Secondary — Farm Customers:** Buyers of broilers, layers, day-old chicks, and animal feeds in and around Busia County, Kenya. Browse and order via the public storefront. Many use mobile phones on 4G.

**Tertiary — Staff (stock managers, sales staff):** Enter daily data — sales, feeding records, production logs — via the admin panel. May have limited digital literacy.

## Product Purpose

Busia Chicken Farm is a dual-purpose platform:
1. **Public e-commerce storefront** — lets customers browse, order, and pay for premium poultry products (broilers, layers, chicks, feeds, fresh eggs).
2. **Internal farm management system** — lets the owner and staff track every aspect of operations: flocks, daily production, vaccinations, feeding, sales, expenses, profit/loss, stock, and staff.

Success = the farm generates more revenue with less manual paperwork, and customers receive a trustworthy online buying experience.

## Positioning

The only poultry platform in Busia County that combines a customer-facing shop *and* a full farm ERP in one codebase — no separate POS, no spreadsheet patchwork.

## Operating Context

- Hosted on cPanel shared hosting at `new.decapoli.co.ke`
- PHP 8.2 + MySQL on the same server; no Node in production
- Admin accessed via `/Frontend/admin/` routes
- Public site at `/Frontend/`
- Database: `mrhzdunf_busiachicken` (auto-migrates schema on boot)
- Payments: M-Pesa (Daraja API — not yet wired live), Bank Transfer, Cash on Delivery
- Locale: Kenya, KES currency, Swahili-speaking customers sometimes

## Capabilities and Constraints

**Done:**
- Full public site (homepage, shop, cart, checkout, about, FAQ, contact, products, login, register)
- Admin: 50+ module pages covering flocks, production, vaccinations, sales, daily sales, bulk sales, cashbook, expenses, profit, feeding, feed production, LPO/purchase orders, incoming stock, stores, staff, messages, dropdowns, settings, analytics, bulk import/export, users, permissions, logs, tasks, calendar, hatchery, batches, broiler, egg grading, hub pages

**Unfinished / Gaps identified:**
- M-Pesa STK push — callback endpoint exists (`mpesa-callback.php`) but Daraja credentials not wired
- Email notifications for orders — `mail()` calls exist but no SMTP configured
- `database.local.php` must be created on the server with real cPanel DB credentials (gitignored)
- `.htaccess` was gitignored — must be manually recreated on server after fresh clone
- `busiaadmin.php` redirect shim was gitignored — server needs it recreated
- Admin login default password (`admin123`) must be changed post-deploy
- `setup_production_database.php` may need to be re-run then deleted after fresh deploy
- Real product images — placeholders use stock downloads; farm should supply real photos
- `ADMIN_EXPANSION_PLAN.md` lists additional modules (Animals, Herds, Breeding, Tasks, Calendar, Alerts, Logs, Messages, Feed Plan, Wool, Milk) not yet built

## Brand Commitments

- **Name:** Busia Chicken Farm
- **Colors:** Forest Green `#1B5E20`, Vibrant Green `#4CAF50`, Amber/Gold `#FF8F00`, Golden Yellow `#FFC107`, Deep Navy `#1A1A2E`
- **Fonts:** Outfit (headings), Inter (body), DM Sans (accents) — all Google Fonts
- **Voice:** Confident, warm, professional. Simple English for staff-facing UI ("Add", "Save", "Remove").
- **Logo:** `/Frontend/images/busia logo.png`

## Evidence on Hand

- 50+ admin PHP pages fully built and committed to GitHub
- Full CSS design system (style.css, components.css, animations.css, responsive.css)
- Database schema: 60 tables, auto-migrated
- Sample data: 4 categories, 15 products, 2 users
- Real phone: +254 727 585 599 | Email: info@busiachicken.com
- Address: Nasira AC Sub-location, Busibwabo, Busia County, Kenya

## Product Principles

1. **Simple enough for Grade 7** — every admin screen must be operable by a non-technical user with short labels, clear CTAs, and forgiving forms.
2. **Mobile-first** — both storefront and admin must work on a mid-range Android phone without horizontal scroll.
3. **Earn trust fast** — the public site must communicate freshness, hygiene, and local knowledge before the fold.
4. **Zero manual SQL** — all schema changes auto-migrate; staff never touch a database.
5. **Ship complete** — no placeholder pages, no "coming soon" in production.

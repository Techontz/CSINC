# CSinc91 — Website & CMS

Rebuild of [csinc91.com](https://csinc91.com): a Next.js public website backed by a Laravel API and a Filament-based CMS.

| Part | Path | Stack | Local URL |
| --- | --- | --- | --- |
| Public website | `frontend/` | Next.js 16 (App Router), TypeScript, Tailwind CSS 4 | http://localhost:3091 |
| API + CMS | `backend/` | Laravel 13, Filament 5, MySQL, Sanctum, Spatie Permission | http://127.0.0.1:8291/admin |

## How it fits together

- **All content comes from the CMS.** Pages are composed of sections (hero, services, product rails, FAQ, …) in *Website → Pages*. Products, categories, services, navigation, media and site settings are all managed in the admin. Nothing on the website is hard-coded.
- **Products (“books”)** are CSinc91’s digital startup guides. Published products appear automatically on `/products`, `/products/{slug}` and `/product-category/{slug}`. Drafts, archived, scheduled and trashed products never reach the public API.
- **Instant updates.** Saving anything in the CMS calls `POST {FRONTEND_URL}/api/revalidate` with a shared secret, which purges the website’s cached data for the affected tags.
- **Purchasing** uses Stripe Checkout (hosted). No card data touches these servers. The Stripe webhook marks orders paid and emails the customer expiring, signed download links. Paid PDFs live on private storage and are only streamed for paid orders. Customers can get fresh links any time at `/downloads`.
- **Inquiries** from the contact form (same fields as the previous Zoho form) are stored in *Inbox → Inquiries* and emailed to the notification address in *Site settings*. Spam protection: honeypot, minimum fill time, and per-IP rate limits.
- **Roles:** Super Admin (everything), Admin (everything except team management), Editor (drafts products and edits content; cannot publish, delete, view orders or change settings).

The homepage opens with a **video hero slider** (*Pages → Home → Video hero slider*): each slide has a heading, text, button, background video and poster image. The four bundled clips and most site photography are free stock media from Pexels (Pexels License: free for commercial use), chosen to show a diverse mix of people. Videos live in `backend/database/data/hero/`, photo sources in `backend/database/data/media.json`, and the CSINC91 logo files (SVG) in `backend/database/data/brand/`. All are imported by the seeder and replaceable from the media library.

Legacy URLs from the WordPress site (`/product/…`, `/services-2`, `/shop`, `/refund_returns`, `/my-account`, …) redirect permanently to their new equivalents.

## Local setup

Requirements: PHP 8.4, Composer, Node 24, MySQL 8+.

```bash
# Backend
cd backend
cp .env.example .env            # set DB_* and the SEED_ADMIN_* values
composer install
php artisan key:generate
php artisan migrate --seed      # roles, admin, CSinc91 content, 36 products + covers (downloads from csinc91.com)
php artisan storage:link
npm install && npm run build    # admin theme
php artisan serve --host=127.0.0.1 --port=8291

# Frontend
cd ../frontend
cp .env.example .env.local      # API_URL, NEXT_PUBLIC_SITE_URL, REVALIDATE_SECRET (= backend FRONTEND_REVALIDATE_SECRET)
npm install
npm run dev
```

Sign in at `/admin` with the `SEED_ADMIN_*` credentials from `backend/.env`.

## Tests

```bash
cd backend && php artisan test          # 66 feature tests (MySQL database `csinc_test`)
cd frontend && npm run lint && npm run typecheck && npm test && npm run build
cd frontend && E2E_ADMIN_EMAIL=… E2E_ADMIN_PASSWORD=… npm run test:e2e   # needs both servers running
```

The end-to-end test signs into the admin, creates a product with a cover and a PDF, publishes it, checks the public listing, detail page and SEO tags, edits it, unpublishes it, and confirms it disappears.

## Production checklist

Backend (`backend/.env`):

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://api.your-domain` (must be HTTPS).
- `FRONTEND_URL`, `FRONTEND_REVALIDATE_SECRET` (long random string, same value in the frontend as `REVALIDATE_SECRET`).
- `MAIL_*` with a real SMTP or transactional mail provider (inquiry alerts and download emails depend on it).
- `STRIPE_SECRET` and `STRIPE_WEBHOOK_SECRET`. In Stripe, add a webhook endpoint `https://api.your-domain/api/v1/stripe/webhook` for `checkout.session.completed`, `checkout.session.async_payment_succeeded`, `checkout.session.async_payment_failed`, `checkout.session.expired` and `charge.refunded`.
- `QUEUE_CONNECTION=database` plus a worker (`php artisan queue:work`) so notifications and revalidation run in the background.
- `TRUSTED_PROXIES` set to the Next.js server’s IP so rate limits apply per visitor.
- `php artisan optimize && php artisan filament:optimize`. Remove `SEED_ADMIN_PASSWORD` after the first deploy.

Frontend (`frontend/.env.production`): `API_URL`, `NEXT_PUBLIC_SITE_URL=https://www.your-domain`, `REVALIDATE_SECRET`. Leave `NEXT_IMAGE_ALLOW_LOCAL_IP` unset. Run `npm run build && npm start`.

### Deploying the frontend on Vercel

- **Root Directory:** `frontend` (framework preset: Next.js).
- **Environment variables:** `API_URL=https://api.your-domain/api/v1`, `NEXT_PUBLIC_SITE_URL=https://www.your-domain`, `REVALIDATE_SECRET` (same value as the backend’s `FRONTEND_REVALIDATE_SECRET`).
- The Laravel backend (API, admin, MySQL, file storage) must be hosted separately on a PHP host such as Laravel Cloud, Forge or a VPS. Vercel only runs the Next.js site.
- If the API is unreachable during a build, pages are rendered on request instead of failing the build; redeploy once the API is live to prebuild them.

## Content the client still needs to supply

- **Product PDFs.** The original site’s paid files are access-protected and could not be migrated. Each product shows “Enquire” instead of “Buy” until its PDF is uploaded (*Catalog → Products → Pricing & delivery*). The dashboard’s *Catalogue health* card lists what is missing.
- **Stripe keys** (above). Until they are set, checkout politely asks visitors to contact the team.
- Optional: phone number, office hours, social profiles and a booking link (*Administration → Site settings*).

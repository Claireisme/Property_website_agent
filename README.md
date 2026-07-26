# Estate Agent Platform

Monorepo for the Ireland property platform MVP.

## Apps

- `apps/agency-site` - Laravel agency website and admin backend.
- `apps/main-portal` - Laravel property portal that syncs agency feeds.

## Current Local Logins

- Agency admin URL: `http://127.0.0.1:8000/admin`
- Portal admin URL: `http://127.0.0.1:8001/admin`
- Email: `admin@example.com`
- Password: `password`
- Local feed token: `dev-feed-token`

## Local Commands

From the repo root:

```bash
./scripts/seed-and-sync-local.sh
./scripts/test-all.sh
./scripts/export-agency-local.sh
./scripts/backup-local.sh
./scripts/start-agency-site.sh
./scripts/start-main-portal.sh
```

```bash
cd apps/agency-site
php artisan serve --host=127.0.0.1 --port=8000
php artisan test
php artisan feed:issue-token "Main Portal"
php artisan properties:translate --locale=zh
```

```bash
cd apps/main-portal
php artisan serve --host=127.0.0.1 --port=8001
php artisan sync:agency-feed
php artisan schedule:run
php artisan test
```

## Local Feed

```bash
curl -H "Authorization: Bearer dev-feed-token" \
  http://127.0.0.1:8000/api/feed/v1/properties
```

## Local Sites

- Agency site: `http://127.0.0.1:8000`
- Main portal: `http://127.0.0.1:8001`
- Localized examples: `http://127.0.0.1:8000/zh/properties` and `http://127.0.0.1:8001/zh/properties`

## Agency Demo Watermark

The demo agency site can show a fixed, transparent, repeated `Demo website` watermark over every page. It is intended for `gnd.520.ie` demo deployments.

Enable or disable it in the agency site's `.env`:

```env
DEMO_WATERMARK_ENABLED=true
DEMO_WATERMARK_TEXT="Demo website"
```

To turn it off:

```env
DEMO_WATERMARK_ENABLED=false
```

After changing these values on the server, clear Laravel caches from the agency site directory:

```bash
cd /www/wwwroot/gnd.520.ie
php artisan config:clear && php artisan view:clear && php artisan optimize
```

To add the watermark code manually, add these config values near `url` in `apps/agency-site/config/app.php`:

```php
'demo_watermark_enabled' => env('DEMO_WATERMARK_ENABLED', false),
'demo_watermark_text' => env('DEMO_WATERMARK_TEXT', 'Demo website'),
```

Then add this markup before `</body>` in `apps/agency-site/resources/views/components/layouts/site.blade.php`:

```blade
@if (config('app.demo_watermark_enabled'))
    <div class="demo-watermark" aria-hidden="true">
        @for ($i = 0; $i < 120; $i++)
            <span>{{ config('app.demo_watermark_text', 'Demo website') }}</span>
        @endfor
    </div>
@endif
```

Add this CSS in the same layout's `<style>` block:

```css
.demo-watermark {
    position: fixed;
    inset: -30vh -30vw;
    z-index: 2147483000;
    pointer-events: none;
    user-select: none;
    display: grid;
    grid-template-columns: repeat(6, minmax(220px, 1fr));
    gap: 72px 96px;
    transform: rotate(-45deg);
    opacity: 0.14;
}

.demo-watermark span {
    color: #0f172a;
    font-size: 28px;
    font-weight: 800;
    letter-spacing: 0.06em;
    white-space: nowrap;
}
```

To remove the watermark permanently, delete the markup block, delete the CSS block, optionally remove the two `demo_watermark_*` config entries, then run:

```bash
php artisan view:clear
rm -f storage/framework/views/*.php
php artisan config:clear && php artisan route:clear && php artisan optimize
```

## Multilingual Listings

- English is the source language.
- Frontend languages: English, Polish, Romanian, French, Spanish, Portuguese, Lithuanian, German, and Chinese.
- Non-English pages show a top disclaimer that translated content is only for reference and the English listing controls.
- Agency listings are translated with `php artisan properties:translate`.
- For local demo placeholders without DeepSeek, run `php artisan properties:translate --fake --force`.
- Preferred production setup: configure DeepSeek in the main portal admin under `Translation settings`, set `TRANSLATION_GATEWAY_TOKEN` on the main portal, and point agency sites at the main portal with `TRANSLATION_GATEWAY_URL` and `TRANSLATION_GATEWAY_TOKEN`.
- Fallback standalone setup: set `DEEPSEEK_API_KEY` in `apps/agency-site/.env`, then run `php artisan properties:translate --force`.
- When translation gateway or standalone DeepSeek credentials are configured, newly published agency listings automatically queue one translation job per non-English locale. Run an agency queue worker, for example `php artisan queue:listen --tries=1 --timeout=0`, so those jobs are processed.
- The agency scheduler also runs `php artisan properties:translate` every 15 minutes as a catch-up pass when translation credentials are configured.
- Agency feed includes translations; the main portal imports them during `php artisan sync:agency-feed`.

## Agency Property Categories

The agency `/properties` page supports category filters:

- `category=for_sale` - residential sales
- `category=to_rent` - residential rentals
- `category=commercial` - commercial sale or rental listings
- `category=other` - sites, land, parking, garages, farms, and similar non-standard listings

The same page also supports a collapsed advanced search panel for region-aware county filtering, property type, price range, bedrooms, bathrooms, floor area, BER, facilities, listed date, and sorting.
Property cards show translucent angled status ribbons for available, under offer, sale agreed, sold/withdrawn, and draft states.
Property detail pages include richer descriptions, icon-led feature highlights, a responsive gallery with thumbnails, keyboard navigation, and a full-screen lightbox.
Lead capture now opens from a collapsible enquiry panel beside the property map, while `Make an Offer` opens a buyer bidding access request flow for registration details, interest submission, proof upload, identity checks, agent approval, and later bidding.
The agency admin has a `Buyer access` area for reviewing these requests, and direct bid submission requires an approved buyer access request.
The agency site also includes a `/mortgages` entry point with a buying budget calculator UI for the future mortgage workflow.

## Demo Data

The agency seeder creates a realistic demo set:

- 53 properties across available, under offer, sale agreed, sold, sale, rent, commercial, and other categories
- 3 team members
- property enquiries and valuation requests
- one online offer with audit trail
- generated local property images with WebP variants
- local placeholder translations when using `./scripts/seed-and-sync-local.sh`

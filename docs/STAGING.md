# Backend staging setup (Laravel)

This repo is Laravel 10 + Sanctum.

## Goals

- Provide a separate staging API base URL (recommended: `https://staging.service.blu.gives/api`).
- Keep staging isolated: separate DB, separate keys/secrets, separate PayPal sandbox credentials.
- Make browser access work safely (CORS + Sanctum stateful domains) for the Next.js web app.

## Files added/updated

- `.env.staging.example` – staging environment template (no secrets)
- `config/cors.php` – now reads origins/credentials from env (defaults remain permissive)

## Quick start (staging server)

1. Copy env:
   - Create `.env` from `.env.staging.example` and fill in secrets.
2. Install deps:
   - `composer install --no-dev --optimize-autoloader`
3. Generate app key:
   - `php artisan key:generate`
4. Migrate:
   - `php artisan migrate --force`
5. Storage symlink (if you serve user photos from storage):
   - `php artisan storage:link`
6. Optimize (optional):
   - `php artisan config:cache`
   - `php artisan route:cache`

## CORS + Sanctum (web app support)

If the Next.js site is on `https://staging.blu.gives` and it uses cookie-based auth:

- Set `CORS_ALLOWED_ORIGINS=https://staging.blu.gives`
- Set `CORS_SUPPORTS_CREDENTIALS=true`
- Set `SANCTUM_STATEFUL_DOMAINS=staging.blu.gives,...`
- Set `SESSION_DOMAIN=staging.blu.gives` (or a parent domain if you intentionally share across subdomains)

Mobile apps typically use Bearer tokens, so CORS does not affect them.

## PayPal

Backend provides PayPal Orders endpoints:
- `POST /api/paypal/create-order`
- `POST /api/paypal/capture-order`

And the donation recording endpoint used by web/mobile parity:
- `POST /api/donor-account/payment` (requires auth)

For staging, use PayPal sandbox:
- `PAYPAL_API_BASE=https://api-m.sandbox.paypal.com`

## Queues + scheduler (recommended for staging)

- Set `QUEUE_CONNECTION=database` (or `redis`) and run a worker (supervisor/systemd):
  - `php artisan queue:work --tries=3 --timeout=90`
- Add cron (every minute):
  - `php artisan schedule:run`

## Security note

Do not commit real secrets into the repo. Environment files should stay local/on the server.

# Dynamic Impact Data Pipeline

The static seed data in `ImpactExampleSeeder` is still available as a fallback, but the donation flow now prefers **impact snapshots** that can be refreshed from an external data feed.

## Snapshot table

`impact_snapshots` stores the latest, operations-verified cost benchmarks. Each record tracks:

- `country_iso` (ISO-3166 alpha-2 country code)
- `category` (`general`, `education`, etc.)
- `min_usd` / `max_usd` ranges for a representative outcome
- Optional narrative fields (`icon`, `headline`, `description`)
- Local price information (`local_currency`, `local_amount`)
- Provenance (`source`, `observed_at`, `metadata` JSON)

Snapshots are versioned by timestamp so fresher entries automatically take priority. When no snapshot matches, the controller falls back to the seeded examples (first for the requested country, then for the global baseline).

## Import command

Use the new artisan command to sync data from a JSON feed or local file:

```powershell
php artisan impact:sync --file=storage/app/impact-feed.json
php artisan impact:sync --url=https://example.com/impact-feed.json
```

Pass `--truncate` to clear existing snapshots before importing when you want a clean slate.

If `IMPACT_FEED_URL` is set in the environment (and optionally `IMPACT_FEED_API_KEY`), the command is scheduled to run every day at 03:00 server time.

### Feed format

The importer accepts an array (or `{ "data": [...] }`) where each entry resembles the payload below:

```json
{
  "country_iso": "AM",
  "category": "general",
  "min_usd": 5,
  "max_usd": 12,
  "headline": "Fresh groceries for a family",
  "description": "Covers vegetables, bread, and pantry staples for a week.",
  "icon": "groceries",
  "local_currency": "AMD",
  "local_amount": 4700,
  "local_note": "Market basket collected from Yerevan Central Market",
  "source": "Operations weekly price check",
  "observed_at": "2025-10-08T00:00:00+04:00"
}
```

Additional keys (`metadata`, `source_note`, etc.) are merged into the record metadata automatically. Missing narrative fields fall back to the closest seeded example so the UI always has copy to display.

## Cache busting

Each import updates an in-memory version stamp (`impact:snapshot:version`). The API embeds that version in its cache keys, so fresh data becomes visible immediately after a sync without a manual cache flush.

## Frontend signal

When a snapshot includes `observed_at` or `source`, the donation flow now surfaces that context to donors (“Last updated … • Source: …”), building trust in the cost estimates they see.

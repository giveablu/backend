<?php

namespace App\Console\Commands;

use App\Models\ImpactExample;
use App\Models\ImpactSnapshot;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SyncImpactSnapshots extends Command
{
    protected $signature = 'impact:sync {--file=} {--url=} {--truncate : Clear existing snapshots before import}';

    protected $description = 'Synchronise impact snapshots from a JSON feed or local file';

    public function handle(): int
    {
        $entries = $this->loadEntries();

        if ($entries === null) {
            return self::FAILURE;
        }

        if ($entries->isEmpty()) {
            $this->warn('No impact snapshot records found in provided feed.');
            return self::SUCCESS;
        }

        if ($this->option('truncate')) {
            ImpactSnapshot::truncate();
            $this->info('Existing impact snapshots truncated.');
        }

        $created = 0;
        $updated = 0;

        foreach ($entries as $entry) {
            $normalized = $this->normalizeEntry($entry);

            if ($normalized === null) {
                continue;
            }

            $snapshot = ImpactSnapshot::updateOrCreate(
                [
                    'country_iso' => $normalized['country_iso'],
                    'category' => $normalized['category'],
                    'headline' => $normalized['headline'],
                ],
                Arr::except($normalized, ['country_iso', 'category', 'headline'])
            );

            if ($snapshot->wasRecentlyCreated) {
                $created++;
            } elseif ($snapshot->wasChanged()) {
                $updated++;
            }
        }

        Cache::forever('impact:snapshot:version', now()->timestamp);

        $this->info(sprintf('Impact snapshots synced. Created: %d, Updated: %d, Total processed: %d', $created, $updated, $entries->count()));

        return self::SUCCESS;
    }

    private function loadEntries(): ?Collection
    {
        if ($file = $this->option('file')) {
            $pathCandidates = [
                $file,
                base_path($file),
                storage_path($file),
                storage_path("app/{$file}"),
                base_path("storage/app/{$file}"),
            ];

            foreach ($pathCandidates as $candidate) {
                if (is_file($candidate)) {
                    $contents = file_get_contents($candidate);
                    if ($contents === false) {
                        $this->error("Unable to read file: {$candidate}");
                        return null;
                    }

                    return $this->decodeEntries($contents, "file:{$candidate}");
                }
            }

            $this->error("Impact feed file not found: {$file}");
            return null;
        }

        $url = $this->option('url') ?? config('services.impact_feed.url');

        if (!$url) {
            $this->error('No impact feed URL configured. Provide --url option or set IMPACT_FEED_URL in environment.');
            return null;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders($this->resolveAuthHeaders())
                ->acceptJson()
                ->get($url);
        } catch (\Throwable $exception) {
            $this->error(sprintf('Request to impact feed failed: %s', $exception->getMessage()));
            return null;
        }

        if (!$response->successful()) {
            $this->error(sprintf('Impact feed request failed with HTTP %d: %s', $response->status(), $response->body()));
            return null;
        }

        $decoded = $response->json();

        if ($decoded === null) {
            $decoded = $this->safeJsonDecode($response->body());
            if ($decoded === null) {
                $this->error('Unable to decode impact feed response as JSON.');
                return null;
            }
        }

        return $this->wrapEntries($decoded, "url:{$url}");
    }

    private function decodeEntries(string $contents, string $context): ?Collection
    {
        $decoded = $this->safeJsonDecode($contents);

        if ($decoded === null) {
            $this->error("Impact feed in {$context} is not valid JSON.");
            return null;
        }

        return $this->wrapEntries($decoded, $context);
    }

    private function safeJsonDecode(string $payload): ?array
    {
        $decoded = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    private function wrapEntries(mixed $decoded, string $context): ?Collection
    {
        if (is_array($decoded) && array_key_exists('data', $decoded) && is_array($decoded['data'])) {
            $decoded = $decoded['data'];
        }

        if (!is_array($decoded)) {
            $this->error("Impact feed {$context} must decode to an array of records.");
            return null;
        }

        return collect($decoded)->filter(static fn ($item) => is_array($item));
    }

    private function resolveAuthHeaders(): array
    {
        $headers = [];
        $apiKey = config('services.impact_feed.api_key');

        if ($apiKey) {
            $headers['Authorization'] = sprintf('Bearer %s', $apiKey);
        }

        return $headers;
    }

    private function normalizeEntry(array $entry): ?array
    {
        $country = strtoupper((string) ($entry['country_iso'] ?? $entry['country'] ?? ''));
        if (strlen($country) !== 2) {
            $this->warn('Skipping entry without valid country ISO code.');
            return null;
        }

        $category = strtolower((string) ($entry['category'] ?? 'general'));
        $minUsd = $this->toFloat($entry['min_usd'] ?? $entry['usd_min'] ?? $entry['min']);
        $maxUsd = $this->toFloat($entry['max_usd'] ?? $entry['usd_max'] ?? $entry['max']);

        if ($minUsd === null || $maxUsd === null) {
            $this->warn(sprintf('Skipping %s/%s entry without numeric min/max USD values.', $country, $category));
            return null;
        }

        if ($minUsd > $maxUsd) {
            [$minUsd, $maxUsd] = [$maxUsd, $minUsd];
        }

        $headline = trim((string) ($entry['headline'] ?? ''));
        $description = trim((string) ($entry['description'] ?? ''));
        $icon = $entry['icon'] ?? null;

        if ($headline === '' || $description === '') {
            $fallback = $this->resolveFallbackExample($country, $category);
            $headline = $headline === '' ? ($fallback?->headline ?? 'Direct support for families') : $headline;
            $description = $description === '' ? ($fallback?->description ?? 'Flexible cash assistance delivered with dignity.') : $description;
            $icon = $icon ?? $fallback?->icon;
        }

        $localCurrency = $entry['local_currency'] ?? ($entry['currency'] ?? null);
        $localAmount = $this->toFloat($entry['local_amount'] ?? $entry['amount_local'] ?? null);
        $source = $entry['source'] ?? $entry['data_source'] ?? null;
        $metadata = $this->resolveMetadata($entry);
        $observedAt = $this->parseObservedAt($entry['observed_at'] ?? $entry['updated_at'] ?? null);

        return [
            'country_iso' => $country,
            'category' => $category,
            'headline' => $headline,
            'description' => $description,
            'icon' => $icon,
            'min_usd' => $minUsd,
            'max_usd' => $maxUsd,
            'local_currency' => $localCurrency ? strtoupper((string) $localCurrency) : null,
            'local_amount' => $localAmount,
            'source' => $source,
            'metadata' => $metadata,
            'observed_at' => $observedAt,
        ];
    }

    private function resolveFallbackExample(string $country, string $category): ?ImpactExample
    {
        return ImpactExample::query()
            ->where('country_iso', $country)
            ->where('category', $category)
            ->orderBy('min_usd')
            ->first()
            ?? ImpactExample::query()
                ->where('country_iso', 'GL')
                ->where('category', $category)
                ->orderBy('min_usd')
                ->first();
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $normalized = preg_replace('/[^0-9.\-]/', '', $value);
            if ($normalized === '' || !is_numeric($normalized)) {
                return null;
            }

            return (float) $normalized;
        }

        return null;
    }

    private function resolveMetadata(array $entry): array
    {
        $metadata = [];

        if (isset($entry['metadata']) && is_array($entry['metadata'])) {
            $metadata = $entry['metadata'];
        }

        foreach (['local_note', 'category_note', 'source_note'] as $key) {
            if (isset($entry[$key]) && !isset($metadata[$key])) {
                $metadata[$key] = $entry[$key];
            }
        }

        return $metadata;
    }

    private function parseObservedAt(mixed $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}

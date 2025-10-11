<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImpactExample;
use App\Models\ImpactSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ImpactController extends Controller
{
    public function estimate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['nullable', 'string', 'size:2'],
            'amount' => ['required', 'numeric', 'min:5', 'max:50'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $country = strtoupper($validated['country'] ?? 'GL');
        $category = strtolower($validated['category'] ?? 'general');
        $amount = min(50.0, max(5.0, (float) $validated['amount']));
        $roundedAmount = max(5, min(50, (int) round($amount)));
        $cacheVersion = Cache::get('impact:snapshot:version', 0);

        $cacheKey = sprintf('impact:estimate:%s:%s:%d:%s', $country, $category, $roundedAmount, $cacheVersion);

        $payload = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($country, $category, $amount, $roundedAmount) {
            $record = $this->resolveImpactRecord($country, $category, $amount, $roundedAmount);

            if (!$record) {
                return null;
            }

            $metadata = is_array($record['metadata'] ?? null) ? $record['metadata'] : [];
            $metadata['local_currency'] = $metadata['local_currency'] ?? ($record['local_currency'] ?? null);

            if (!isset($metadata['local_amount']) && array_key_exists('local_amount', $record) && $record['local_amount'] !== null) {
                $metadata['local_amount'] = (float) $record['local_amount'];
            }

            $midpoint = ($record['min_usd'] + $record['max_usd']) / 2;
            $conversionRatio = null;

            if ($midpoint > 0) {
                if (isset($metadata['local_amount']) && $metadata['local_amount']) {
                    $conversionRatio = (float) $metadata['local_amount'] / $midpoint;
                }
            }

            $estimatedLocalAmount = $conversionRatio ? round($conversionRatio * $amount) : null;

            return [
                'example' => [
                    'id' => $record['id'],
                    'country' => $record['country_iso'],
                    'category' => $record['category'],
                    'icon' => $record['icon'],
                    'headline' => $record['headline'],
                    'description' => $record['description'],
                    'min_usd' => (float) $record['min_usd'],
                    'max_usd' => (float) $record['max_usd'],
                    'metadata' => $metadata,
                    'source' => $record['source'] ?? null,
                    'observed_at' => $record['observed_at'] ?? null,
                ],
                'amount' => [
                    'usd' => $amount,
                    'rounded_usd' => $roundedAmount,
                    'local_currency' => $metadata['local_currency'] ?? null,
                    'estimated_local_amount' => $estimatedLocalAmount,
                ],
            ];
        });

        if (!$payload) {
            return response()->json([
                'response' => false,
                'message' => 'Impact data not available for this selection yet.',
            ], 404);
        }

        return response()->json([
            'response' => true,
            'data' => $payload,
        ]);
    }

    private function resolveImpactRecord(string $country, string $category, float $amount, int $roundedAmount): ?array
    {
        $categories = array_values(array_unique([$category, 'general']));

        foreach ($categories as $searchCategory) {
            $snapshot = $this->findSnapshot($country, $searchCategory, $amount, $roundedAmount);
            if ($snapshot) {
                return $this->transformSnapshot($snapshot);
            }

            if ($country !== 'GL') {
                $globalSnapshot = $this->findSnapshot('GL', $searchCategory, $amount, $roundedAmount);
                if ($globalSnapshot) {
                    return $this->transformSnapshot($globalSnapshot);
                }
            }

            $example = $this->findExample($country, $searchCategory, $amount, $roundedAmount);
            if ($example) {
                return $this->transformExample($example);
            }

            if ($country !== 'GL') {
                $globalExample = $this->findExample('GL', $searchCategory, $amount, $roundedAmount);
                if ($globalExample) {
                    return $this->transformExample($globalExample);
                }
            }
        }

        return null;
    }

    private function findSnapshot(string $country, string $category, float $amount, int $roundedAmount): ?ImpactSnapshot
    {
        $query = ImpactSnapshot::query()
            ->where('country_iso', $country)
            ->where('category', $category);

        $inRange = (clone $query)
            ->where('min_usd', '<=', $amount)
            ->where('max_usd', '>=', $amount)
            ->orderByDesc('observed_at')
            ->orderByDesc('updated_at')
            ->orderBy('min_usd')
            ->first();

        if ($inRange) {
            return $inRange;
        }

        return $query
            ->orderByRaw('ABS(((min_usd + max_usd) / 2) - ?) ASC', [$roundedAmount])
            ->orderByDesc('observed_at')
            ->orderByDesc('updated_at')
            ->orderBy('min_usd')
            ->first();
    }

    private function findExample(string $country, string $category, float $amount, int $roundedAmount): ?ImpactExample
    {
        $query = ImpactExample::query()
            ->where('country_iso', $country)
            ->where('category', $category);

        $inRange = (clone $query)
            ->where('min_usd', '<=', $amount)
            ->where('max_usd', '>=', $amount)
            ->orderBy('min_usd')
            ->first();

        if ($inRange) {
            return $inRange;
        }

        return $query
            ->orderByRaw('ABS(((min_usd + max_usd) / 2) - ?) ASC', [$roundedAmount])
            ->orderBy('min_usd')
            ->first();
    }

    private function transformSnapshot(ImpactSnapshot $snapshot): array
    {
        $metadata = $snapshot->metadata ?? [];

        if (!isset($metadata['local_currency']) && $snapshot->local_currency) {
            $metadata['local_currency'] = $snapshot->local_currency;
        }

        if (!isset($metadata['local_amount']) && $snapshot->local_amount !== null) {
            $metadata['local_amount'] = (float) $snapshot->local_amount;
        }

        if ($snapshot->source && !isset($metadata['source'])) {
            $metadata['source'] = $snapshot->source;
        }

        if ($snapshot->observed_at && !isset($metadata['observed_at'])) {
            $metadata['observed_at'] = $snapshot->observed_at->toIso8601String();
        }

        return [
            'id' => $snapshot->id,
            'country_iso' => $snapshot->country_iso,
            'category' => $snapshot->category,
            'icon' => $snapshot->icon,
            'headline' => $snapshot->headline,
            'description' => $snapshot->description,
            'min_usd' => (float) $snapshot->min_usd,
            'max_usd' => (float) $snapshot->max_usd,
            'metadata' => $metadata,
            'source' => $snapshot->source,
            'observed_at' => $snapshot->observed_at ? $snapshot->observed_at->toIso8601String() : null,
            'local_currency' => $snapshot->local_currency,
            'local_amount' => $snapshot->local_amount ? (float) $snapshot->local_amount : null,
        ];
    }

    private function transformExample(ImpactExample $example): array
    {
        $metadata = $example->metadata ?? [];

        if (!isset($metadata['local_currency']) && isset($metadata['currency'])) {
            $metadata['local_currency'] = $metadata['currency'];
        }

        return [
            'id' => $example->id,
            'country_iso' => $example->country_iso,
            'category' => $example->category,
            'icon' => $example->icon,
            'headline' => $example->headline,
            'description' => $example->description,
            'min_usd' => (float) $example->min_usd,
            'max_usd' => (float) $example->max_usd,
            'metadata' => $metadata,
            'source' => $metadata['source'] ?? null,
            'observed_at' => $metadata['observed_at'] ?? null,
            'local_currency' => $metadata['local_currency'] ?? null,
            'local_amount' => isset($metadata['local_amount']) ? (float) $metadata['local_amount'] : null,
        ];
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class LocationOptions
{
    protected static ?array $countries = null;
    protected static ?array $states = null;
    protected static ?array $cities = null;

    protected static ?array $countryIndex = null;
    protected static array $stateIndex = [];
    protected static array $cityIndex = [];

    public static function countryOptions(): array
    {
        return array_values(array_map(
            fn (array $country) => Arr::only($country, ['code', 'name', 'phoneCode', 'currency']),
            self::loadCountries()
        ));
    }

    public static function matchCountry(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $countries = self::loadCountries();
        $upper = mb_strtoupper($trimmed);
        foreach ($countries as $country) {
            if (mb_strtoupper($country['code']) === $upper) {
                return $country;
            }
        }

        $normalizedSearch = self::normalize($trimmed);
        foreach ($countries as $country) {
            if ($country['normalized'] === $normalizedSearch['normalized']) {
                return $country;
            }
        }

        foreach ($countries as $country) {
            if (self::matchesAlternatives($country['alternatives'], $normalizedSearch)) {
                return $country;
            }
        }

        foreach ($countries as $country) {
            if (str_contains($country['normalized'], $normalizedSearch['normalized'])) {
                return $country;
            }
        }

        return null;
    }

    public static function findCountryByCode(?string $code): ?array
    {
        if ($code === null || $code === '') {
            return null;
        }

        $upper = mb_strtoupper($code);
        $countries = self::loadCountries();

        foreach ($countries as $country) {
            if (mb_strtoupper($country['code']) === $upper) {
                return $country;
            }
        }

        return null;
    }

    public static function stateOptions(string $countryCode): array
    {
        $upper = mb_strtoupper($countryCode);
        return array_values(array_map(
            fn (array $state) => Arr::only($state, ['code', 'name', 'countryCode', 'latitude', 'longitude']),
            self::loadStatesForCountry($upper)
        ));
    }

    public static function findStateByCode(string $countryCode, string $stateCode): ?array
    {
        $upperCountry = mb_strtoupper($countryCode);
        $upperState = mb_strtoupper($stateCode);

        foreach (self::loadStatesForCountry($upperCountry) as $state) {
            if (mb_strtoupper($state['code']) === $upperState) {
                return $state;
            }
        }

        return null;
    }

    public static function matchState(?string $countryCode, ?string $value): ?array
    {
        if ($countryCode === null || $countryCode === '' || $value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $upperCountry = mb_strtoupper($countryCode);
        $states = self::loadStatesForCountry($upperCountry);
        $upper = mb_strtoupper($trimmed);

        foreach ($states as $state) {
            if (mb_strtoupper($state['code']) === $upper) {
                return $state;
            }
        }

        $normalizedSearch = self::normalize($trimmed);
        foreach ($states as $state) {
            if ($state['normalized'] === $normalizedSearch['normalized']) {
                return $state;
            }
        }

        foreach ($states as $state) {
            if (self::matchesAlternatives($state['alternatives'], $normalizedSearch)) {
                return $state;
            }
        }

        foreach ($states as $state) {
            if (str_contains($state['normalized'], $normalizedSearch['normalized'])) {
                return $state;
            }
        }

        return null;
    }

    public static function cityOptions(string $countryCode, string $stateCode, int $limit = 50): array
    {
        $cities = self::loadCitiesForState(mb_strtoupper($countryCode), mb_strtoupper($stateCode));

        return array_values(array_map(
            fn (array $city) => Arr::only($city, ['name', 'stateCode', 'countryCode', 'latitude', 'longitude']),
            array_slice($cities, 0, max($limit, 1))
        ));
    }

    public static function matchCity(?string $countryCode, ?string $stateCode, ?string $value): ?array
    {
        if ($countryCode === null || $countryCode === '' || $stateCode === null || $stateCode === '' || $value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $cities = self::loadCitiesForState(mb_strtoupper($countryCode), mb_strtoupper($stateCode));
        $normalizedSearch = self::normalize($trimmed);

        foreach ($cities as $city) {
            if ($city['normalized'] === $normalizedSearch['normalized']) {
                return $city;
            }
        }

        foreach ($cities as $city) {
            if (self::matchesAlternatives($city['alternatives'], $normalizedSearch)) {
                return $city;
            }
        }

        foreach ($cities as $city) {
            if (str_contains($city['normalized'], $normalizedSearch['normalized'])) {
                return $city;
            }
        }

        return null;
    }

    public static function findCity(string $countryCode, string $stateCode, string $name): ?array
    {
        $cities = self::loadCitiesForState(mb_strtoupper($countryCode), mb_strtoupper($stateCode));
        $normalizedSearch = self::normalize($name);

        foreach ($cities as $city) {
            if ($city['normalized'] === $normalizedSearch['normalized']) {
                return $city;
            }
        }

        return null;
    }

    public static function searchCities(string $countryCode, string $stateCode, string $term, int $limit = 25): array
    {
        $cities = self::loadCitiesForState(mb_strtoupper($countryCode), mb_strtoupper($stateCode));
        $normalizedSearch = self::normalize($term);

        if ($normalizedSearch['normalized'] === '') {
            return array_values(array_map(
                fn (array $city) => Arr::only($city, ['name', 'stateCode', 'countryCode']),
                array_slice($cities, 0, max($limit, 1))
            ));
        }

        $results = [];
        foreach ($cities as $city) {
            if ($city['normalized'] === $normalizedSearch['normalized'] ||
                self::matchesAlternatives($city['alternatives'], $normalizedSearch) ||
                str_contains($city['normalized'], $normalizedSearch['normalized'])) {
                $results[] = Arr::only($city, ['name', 'stateCode', 'countryCode']);
                if (count($results) >= $limit) {
                    break;
                }
            }
        }

        return $results;
    }

    protected static function loadCountries(): array
    {
        if (self::$countries !== null) {
            return self::$countries;
        }

        $path = resource_path('data/countries.json');
        $raw = is_file($path) ? json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR) : [];

        self::$countries = array_map(function (array $country): array {
            $normalized = self::normalize($country['name'] ?? '');

            return [
                'code' => $country['code'] ?? '',
                'name' => $country['name'] ?? '',
                'phoneCode' => $country['phoneCode'] ?? null,
                'currency' => $country['currency'] ?? null,
                'normalized' => $normalized['normalized'],
                'alternatives' => $normalized['alternatives'],
            ];
        }, $raw);

        return self::$countries;
    }

    protected static function loadStates(): array
    {
        if (self::$states !== null) {
            return self::$states;
        }

        $path = resource_path('data/states.json');
        $raw = is_file($path) ? json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR) : [];

        self::$states = array_map(function (array $state): array {
            $normalized = self::normalize($state['name'] ?? '');

            return [
                'code' => $state['code'] ?? '',
                'name' => $state['name'] ?? '',
                'countryCode' => $state['countryCode'] ?? '',
                'latitude' => $state['latitude'] ?? null,
                'longitude' => $state['longitude'] ?? null,
                'normalized' => $normalized['normalized'],
                'alternatives' => $normalized['alternatives'],
            ];
        }, $raw);

        return self::$states;
    }

    protected static function loadCities(): array
    {
        if (self::$cities !== null) {
            return self::$cities;
        }

        $path = resource_path('data/cities.json');
        $raw = is_file($path) ? json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR) : [];

        self::$cities = array_map(function (array $city): array {
            $normalized = self::normalize($city['name'] ?? '');

            return [
                'name' => $city['name'] ?? '',
                'stateCode' => $city['stateCode'] ?? '',
                'countryCode' => $city['countryCode'] ?? '',
                'latitude' => $city['latitude'] ?? null,
                'longitude' => $city['longitude'] ?? null,
                'normalized' => $normalized['normalized'],
                'alternatives' => $normalized['alternatives'],
            ];
        }, $raw);

        return self::$cities;
    }

    protected static function loadStatesForCountry(string $countryCode): array
    {
        $upper = mb_strtoupper($countryCode);

        if (! isset(self::$stateIndex[$upper])) {
            $states = array_values(array_filter(
                self::loadStates(),
                fn (array $state) => mb_strtoupper($state['countryCode']) === $upper
            ));

            usort($states, fn (array $a, array $b) => strcmp($a['name'], $b['name']));

            self::$stateIndex[$upper] = $states;
        }

        return self::$stateIndex[$upper];
    }

    protected static function loadCitiesForState(string $countryCode, string $stateCode): array
    {
        $key = $countryCode . '-' . $stateCode;

        if (! isset(self::$cityIndex[$key])) {
            $cities = array_values(array_filter(
                self::loadCities(),
                fn (array $city) => mb_strtoupper($city['countryCode']) === $countryCode
                    && mb_strtoupper($city['stateCode']) === $stateCode
            ));

            usort($cities, fn (array $a, array $b) => strcmp($a['name'], $b['name']));

            self::$cityIndex[$key] = $cities;
        }

        return self::$cityIndex[$key];
    }

    protected static function normalize(?string $value): array
    {
        if ($value === null) {
            return ['normalized' => '', 'alternatives' => []];
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return ['normalized' => '', 'alternatives' => []];
        }

        $normalized = self::asciiLower($trimmed);
        $collapsed = preg_replace('/[^a-z0-9]/', '', $normalized) ?? '';
        $withoutSpaces = str_replace(' ', '', $normalized);

        $alternatives = array_values(array_unique(array_filter([$collapsed, $withoutSpaces], fn ($token) => $token !== '')));

        return [
            'normalized' => $normalized,
            'alternatives' => $alternatives,
        ];
    }

    protected static function asciiLower(string $value): string
    {
        $normalized = class_exists(\Normalizer::class)
            ? \Normalizer::normalize($value, \Normalizer::FORM_KD)
            : $value;

        $stripped = preg_replace('/[\p{Mn}]+/u', '', $normalized ?? $value) ?? $value;
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $stripped);

        $result = $transliterated !== false ? $transliterated : $stripped;

        return mb_strtolower($result);
    }

    protected static function matchesAlternatives(array $alternatives, array $search): bool
    {
        foreach ($alternatives as $candidate) {
            if ($candidate === $search['normalized']) {
                return true;
            }

            if (in_array($candidate, $search['alternatives'], true)) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Support\LocationOptions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AdminUser extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public string $statusFilter = 'all';
    public string $roleFilter = 'all';
    public int $perPage = 10;

    public ?int $selectedUserId = null;
    public bool $showUserDrawer = false;

    public array $editForm = [
        'name' => '',
        'email' => '',
        'phone' => '',
        'role' => 'donor',
        'status' => 'active',
        'email_verified' => false,
        'phone_verified' => false,
        'profile_description' => '',
        'country' => '',
        'country_code' => '',
        'region' => '',
        'state_code' => '',
        'city' => '',
        'post_amount' => '',
        'post_biography' => '',
        'hardship_ids' => [],
    ];

    public array $selectedUserMetrics = [];

    public array $countryOptions = [];
    public array $stateOptions = [];
    public array $citySuggestions = [];

    public string $countryInput = '';
    public string $stateInput = '';
    public string $cityInput = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => 'all'],
        'roleFilter' => ['except' => 'all'],
    ];

    protected $validationAttributes = [
        'editForm.name' => 'name',
        'editForm.email' => 'email address',
        'editForm.phone' => 'phone number',
        'editForm.role' => 'role',
        'editForm.status' => 'status',
    ];

    public function mount(): void
    {
        $this->countryOptions = LocationOptions::countryOptions();
    }

    public function addUser(): void
    {
        $this->redirectRoute('admin.users.create');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function selectUser(int $userId): void
    {
        $user = User::query()
            ->where('role', '!=', 'admin')
            ->findOrFail($userId);

        $this->selectedUserId = $user->id;
        $this->showUserDrawer = true;

        $user->loadMissing(['post.tags']);

        $post = $user->post;
        $this->editForm = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'status' => $user->status ?? 'active',
            'email_verified' => (bool) $user->email_verified_at,
            'phone_verified' => (bool) $user->phone_verified_at,
            'profile_description' => $user->profile_description ?? '',
            'country' => $user->country ?? '',
            'region' => $user->region ?? '',
            'city' => $user->city ?? '',
            'post_amount' => $post->amount ?? '',
            'post_biography' => $post->biography ?? '',
            'hardship_ids' => $post?->tags->pluck('id')->all() ?? [],
        ];

        $this->selectedUserMetrics = $this->buildMetrics($user);

        $this->countryInput = $this->editForm['country'] ?? '';
        $this->stateInput = $this->editForm['region'] ?? '';
        $this->cityInput = $this->editForm['city'] ?? '';

        $this->syncLocationSelectionsFromInputs();
    }

    public function clearSelection(): void
    {
        $this->selectedUserId = null;
        $this->showUserDrawer = false;
        $this->selectedUserMetrics = [];
        $this->editForm = [
            'name' => '',
            'email' => '',
            'phone' => '',
            'role' => 'donor',
            'status' => 'active',
            'email_verified' => false,
            'phone_verified' => false,
            'profile_description' => '',
            'country' => '',
            'country_code' => '',
            'region' => '',
            'state_code' => '',
            'city' => '',
            'post_amount' => '',
            'post_biography' => '',
            'hardship_ids' => [],
        ];
        $this->countryInput = '';
        $this->stateInput = '';
        $this->cityInput = '';
        $this->stateOptions = [];
        $this->citySuggestions = [];
    }

    public function saveUser(): void
    {
        if (!$this->selectedUserId) {
            return;
        }

        $user = User::query()
            ->where('role', '!=', 'admin')
            ->findOrFail($this->selectedUserId);

        $rules = [
            'editForm.name' => ['required', 'string', 'max:255'],
            'editForm.email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'editForm.phone' => ['nullable', 'string', 'max:32', Rule::unique('users', 'phone')->ignore($user->id)],
            'editForm.role' => ['required', Rule::in(['donor', 'receiver'])],
            'editForm.status' => ['required', Rule::in(['active', 'suspended'])],
            'editForm.email_verified' => ['boolean'],
            'editForm.phone_verified' => ['boolean'],
        ];

        if (($this->editForm['role'] ?? null) === 'receiver') {
            $rules = array_merge($rules, [
                'editForm.profile_description' => ['nullable', 'string', 'max:1000'],
                'editForm.country' => ['nullable', 'string', 'max:150'],
                'editForm.region' => ['nullable', 'string', 'max:150'],
                'editForm.city' => ['nullable', 'string', 'max:150'],
                'editForm.post_amount' => ['nullable', 'string', 'max:10'],
                'editForm.post_biography' => ['nullable', 'string', 'max:500'],
                'editForm.hardship_ids' => ['nullable', 'array'],
                'editForm.hardship_ids.*' => ['integer', Rule::exists('tags', 'id')],
            ]);
        }

        $this->validate($rules);

        $profileDescription = $this->sanitizeNullableString($this->editForm['profile_description'] ?? null);
        $country = $this->sanitizeNullableString($this->editForm['country'] ?? null);
        $region = $this->sanitizeNullableString($this->editForm['region'] ?? null);
        $city = $this->sanitizeNullableString($this->editForm['city'] ?? null);

        [$country, $region, $city] = $this->resolveLocationValues($country, $region, $city);

        $user->forceFill([
            'name' => $this->editForm['name'],
            'email' => $this->editForm['email'],
            'phone' => $this->editForm['phone'],
            'role' => $this->editForm['role'],
            'status' => $this->editForm['status'],
            'profile_description' => $profileDescription,
            'country' => $country,
            'region' => $region,
            'city' => $city,
        ]);

        $user->email_verified_at = $this->editForm['email_verified'] ? ($user->email_verified_at ?? now()) : null;
        $user->phone_verified_at = $this->editForm['phone_verified'] ? ($user->phone_verified_at ?? now()) : null;
        $user->save();

        $user->refresh();
        $this->editForm['email_verified'] = (bool) $user->email_verified_at;
        $this->editForm['phone_verified'] = (bool) $user->phone_verified_at;
        $this->selectedUserMetrics = $this->buildMetrics($user);

        $this->editForm['profile_description'] = $profileDescription ?? '';
        $this->editForm['country'] = $country ?? '';
        $this->editForm['region'] = $region ?? '';
        $this->editForm['city'] = $city ?? '';

        if ($user->role === 'receiver') {
            $postAmount = $this->sanitizeNullableString($this->editForm['post_amount'] ?? null);
            $postBiography = $this->sanitizeNullableString($this->editForm['post_biography'] ?? null);
            $hardshipIds = collect($this->editForm['hardship_ids'] ?? [])
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $hasPostContent = $postAmount !== null || $postBiography !== null || !empty($hardshipIds);

            if ($hasPostContent) {
                $post = Post::firstOrNew(['user_id' => $user->id]);
                $post->amount = $postAmount;
                $post->biography = $postBiography;
                $post->save();
                $post->tags()->sync($hardshipIds);
            } elseif ($user->post) {
                $user->post->tags()->sync([]);
                $user->post->update([
                    'amount' => null,
                    'biography' => null,
                ]);
            }

            $this->editForm['post_amount'] = $postAmount ?? '';
            $this->editForm['post_biography'] = $postBiography ?? '';
            $this->editForm['hardship_ids'] = $hardshipIds;
        } else {
            if ($user->post) {
                $user->post->tags()->sync([]);
            }

            $this->editForm['profile_description'] = '';
            $this->editForm['country'] = '';
            $this->editForm['region'] = '';
            $this->editForm['city'] = '';
            $this->editForm['post_amount'] = '';
            $this->editForm['post_biography'] = '';
            $this->editForm['hardship_ids'] = [];
        }

        session()->flash('message', 'User details updated.');
        $this->dispatch('user-message');

        $this->syncLocationSelectionsFromInputs();
    }

    public function updatedCountryInput(string $value): void
    {
        $match = LocationOptions::matchCountry($value);

        if ($match) {
            $previousCountry = $this->editForm['country_code'] ?? '';
            $this->editForm['country_code'] = $match['code'];
            $this->editForm['country'] = $match['name'];
            $this->countryInput = $match['name'];

            if (mb_strtoupper($previousCountry) !== mb_strtoupper($match['code'])) {
                $this->stateOptions = LocationOptions::stateOptions($match['code']);
                $this->editForm['state_code'] = '';
                $this->stateInput = '';
                $this->editForm['region'] = '';
                $this->cityInput = '';
                $this->editForm['city'] = '';
                $this->citySuggestions = [];
            }
        } else {
            $this->editForm['country_code'] = '';
            $this->editForm['country'] = $value;
            $this->stateOptions = [];
            $this->editForm['state_code'] = '';
            $this->stateInput = '';
            $this->editForm['region'] = '';
            $this->cityInput = '';
            $this->editForm['city'] = '';
            $this->citySuggestions = [];
        }
    }

    public function updatedStateInput(string $value): void
    {
        $this->editForm['region'] = $value;
        $countryCode = $this->editForm['country_code'] ?? '';

        if ($countryCode === '') {
            $this->editForm['state_code'] = '';
            $this->citySuggestions = [];
            return;
        }

        $match = LocationOptions::matchState($countryCode, $value);

        if ($match) {
            $this->editForm['state_code'] = $match['code'];
            $this->stateInput = $match['name'];
            $this->editForm['region'] = $match['name'];
            $this->stateOptions = LocationOptions::stateOptions($countryCode);
            $this->citySuggestions = LocationOptions::cityOptions($countryCode, $match['code'], 50);

            $cityMatch = LocationOptions::matchCity($countryCode, $match['code'], $this->cityInput);
            if ($cityMatch) {
                $this->cityInput = $cityMatch['name'];
                $this->editForm['city'] = $cityMatch['name'];
            } elseif ($this->cityInput !== '') {
                $this->cityInput = '';
                $this->editForm['city'] = '';
            }
        } else {
            $this->editForm['state_code'] = '';
            $this->citySuggestions = [];
        }
    }

    public function updatedCityInput(string $value): void
    {
        $this->editForm['city'] = $value;
        $countryCode = $this->editForm['country_code'] ?? '';
        $stateCode = $this->editForm['state_code'] ?? '';

        if ($countryCode === '' || $stateCode === '') {
            $this->citySuggestions = [];
            return;
        }

        $match = LocationOptions::matchCity($countryCode, $stateCode, $value);
        if ($match) {
            $this->cityInput = $match['name'];
            $this->editForm['city'] = $match['name'];
        }

        $this->citySuggestions = LocationOptions::searchCities($countryCode, $stateCode, $value, 25);
    }

    public function toggleStatus(): void
    {
        if (!$this->selectedUserId) {
            return;
        }

        $user = User::query()
            ->where('role', '!=', 'admin')
            ->findOrFail($this->selectedUserId);

        $user->status = $user->status === 'suspended' ? 'active' : 'suspended';
        $user->save();

        $this->editForm['status'] = $user->status;
        $this->selectedUserMetrics = $this->buildMetrics($user->refresh());

        session()->flash('message', $user->status === 'active' ? 'User reactivated.' : 'User suspended.');
        $this->dispatch('user-message');
    }

    public function render()
    {
        $usersQuery = User::query()
            ->where('role', '!=', 'admin');

        $usersQuery = $this->applyFilters($usersQuery);

        $sortField = $this->resolveSortField($this->sortField);
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $users = $usersQuery
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage);

        $summaryQuery = User::query()->where('role', '!=', 'admin');
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'active' => (clone $summaryQuery)->where('status', 'active')->count(),
            'suspended' => (clone $summaryQuery)->where('status', 'suspended')->count(),
            'newThisWeek' => (clone $summaryQuery)->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('livewire.admin.admin-user', [
            'users' => $users,
            'summary' => $summary,
            'availableTags' => Tag::orderBy('name')->get(['id', 'name']),
        ]);
    }

    protected function applyFilters(Builder $query): Builder
    {
        if ($this->search !== '') {
            $searchValue = trim($this->search);
            if ($searchValue !== '') {
                $searchTerm = '%' . $searchValue . '%';
                $columns = $this->resolveSearchableColumns();
                $digitsOnly = preg_replace('/\D+/', '', $searchValue);
                $digitsOnly = $digitsOnly ?? '';
                $shouldSearchPhone = strlen($digitsOnly) >= 3;

                $query->where(function (Builder $inner) use ($columns, $searchTerm, $searchValue, $digitsOnly, $shouldSearchPhone) {
                    foreach ($columns as $column) {
                        if ($column === 'phone') {
                            if ($shouldSearchPhone) {
                                $inner->orWhere(function (Builder $phoneQuery) use ($digitsOnly) {
                                    $phoneQuery
                                        ->whereNotNull('phone')
                                        ->where('phone', 'like', '%' . $digitsOnly . '%');
                                });
                            }

                            continue;
                        }

                        $inner->orWhere($column, 'like', $searchTerm);
                    }

                    if (! in_array('phone', $columns, true) && $shouldSearchPhone) {
                        $inner->orWhere('phone', 'like', '%' . $digitsOnly . '%');
                    }

                    if (ctype_digit($searchValue)) {
                        $inner->orWhere('id', (int) $searchValue);
                    }
                });
            }
        }

        if ($this->roleFilter !== 'all') {
            $query->where('role', $this->roleFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query;
    }

    protected function resolveSortField(string $field): string
    {
        $allowed = ['name', 'email', 'role', 'status', 'created_at', 'last_login_at'];

        return in_array($field, $allowed, true) ? $field : 'created_at';
    }

    protected function buildMetrics(User $user): array
    {
        $lastLogin = $user->last_login_at ? Carbon::parse($user->last_login_at) : null;

        $donationStats = DB::table('donations')
            ->where('user_id', $user->id)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(gross_amount), 0) as total')
            ->first();

        $post = $user->post()
            ->with('tags:id,name')
            ->select($this->resolvePostMetricsColumns())
            ->first();

        $postData = $post ? $post->toArray() : null;

        if (is_array($postData) && ! array_key_exists('activity', $postData)) {
            $postData['activity'] = null;
        }

        if (is_array($postData)) {
            $postData['tags'] = $post?->tags->pluck('name')->all();
        }

        return [
            'search_id' => $user->search_id,
            'registered_at' => $user->created_at,
            'last_login_at' => $user->last_login_at,
            'status' => $user->status,
            'role' => $user->role,
            'email_verified_at' => $user->email_verified_at,
            'phone_verified_at' => $user->phone_verified_at,
            'donations_count' => (int) ($donationStats->count ?? 0),
            'donations_total' => (float) ($donationStats->total ?? 0),
            'post' => $postData,
            'is_online' => $lastLogin ? $lastLogin->greaterThanOrEqualTo(now()->subMinutes(10)) : false,
            'location' => collect([$user->city, $user->region, $user->country])->filter()->implode(', '),
            'profile_description' => $user->profile_description,
        ];
    }

    protected function resolvePostMetricsColumns(): array
    {
        $columns = ['user_id', 'paid', 'amount', 'biography'];

        if (Schema::hasColumn('posts', 'activity')) {
            $columns[] = 'activity';
        }

        return $columns;
    }

    protected function resolveSearchableColumns(): array
    {
        $columns = ['name', 'email'];

        if (Schema::hasColumn('users', 'phone')) {
            $columns[] = 'phone';
        }

        if (Schema::hasColumn('users', 'search_id')) {
            $columns[] = 'search_id';
        }

        return $columns;
    }

    protected function sanitizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = is_string($value) ? $value : (string) $value;
        $trimmed = trim($string);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function resolveLocationValues(?string $country, ?string $region, ?string $city): array
    {
        $countryCode = $this->editForm['country_code'] ?? '';
        $stateCode = $this->editForm['state_code'] ?? '';

        if ($countryCode !== '') {
            $matchedCountry = LocationOptions::findCountryByCode($countryCode);
            if ($matchedCountry) {
                $country = $matchedCountry['name'];
                $this->countryInput = $matchedCountry['name'];
                $this->editForm['country'] = $matchedCountry['name'];
            }
        }

        if ($countryCode !== '' && $stateCode !== '') {
            $matchedState = LocationOptions::findStateByCode($countryCode, $stateCode);
            if ($matchedState) {
                $region = $matchedState['name'];
                $this->stateInput = $matchedState['name'];
                $this->editForm['region'] = $matchedState['name'];
            }

            if ($city !== null && $city !== '') {
                $matchedCity = LocationOptions::matchCity($countryCode, $stateCode, $city);
                if ($matchedCity) {
                    $city = $matchedCity['name'];
                    $this->cityInput = $matchedCity['name'];
                }
            }
        }

        return [$country, $region, $city];
    }

    protected function syncLocationSelectionsFromInputs(): void
    {
        $this->stateOptions = [];
        $this->citySuggestions = [];

        if ($this->countryInput !== '') {
            $match = LocationOptions::matchCountry($this->countryInput);
            if ($match) {
                $this->editForm['country_code'] = $match['code'];
                $this->editForm['country'] = $match['name'];
                $this->countryInput = $match['name'];
                $this->stateOptions = LocationOptions::stateOptions($match['code']);

                if ($this->stateInput !== '') {
                    $stateMatch = LocationOptions::matchState($match['code'], $this->stateInput);
                    if ($stateMatch) {
                        $this->editForm['state_code'] = $stateMatch['code'];
                        $this->editForm['region'] = $stateMatch['name'];
                        $this->stateInput = $stateMatch['name'];
                        $this->citySuggestions = LocationOptions::cityOptions($match['code'], $stateMatch['code'], 50);

                        if ($this->cityInput !== '') {
                            $cityMatch = LocationOptions::matchCity($match['code'], $stateMatch['code'], $this->cityInput);
                            if ($cityMatch) {
                                $this->cityInput = $cityMatch['name'];
                                $this->editForm['city'] = $cityMatch['name'];
                            }
                        }
                    } else {
                        $this->editForm['state_code'] = '';
                    }
                }
            } else {
                $this->editForm['country_code'] = '';
            }
        }
    }
}

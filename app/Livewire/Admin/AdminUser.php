<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
    ];

    public array $selectedUserMetrics = [];

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

        $this->editForm = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'status' => $user->status ?? 'active',
            'email_verified' => (bool) $user->email_verified_at,
            'phone_verified' => (bool) $user->phone_verified_at,
        ];

        $this->selectedUserMetrics = $this->buildMetrics($user);
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
        ];
    }

    public function saveUser(): void
    {
        if (!$this->selectedUserId) {
            return;
        }

        $user = User::query()
            ->where('role', '!=', 'admin')
            ->findOrFail($this->selectedUserId);

        $this->validate([
            'editForm.name' => ['required', 'string', 'max:255'],
            'editForm.email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'editForm.phone' => ['nullable', 'string', 'max:32', Rule::unique('users', 'phone')->ignore($user->id)],
            'editForm.role' => ['required', Rule::in(['donor', 'receiver'])],
            'editForm.status' => ['required', Rule::in(['active', 'suspended'])],
            'editForm.email_verified' => ['boolean'],
            'editForm.phone_verified' => ['boolean'],
        ]);

        $user->forceFill([
            'name' => $this->editForm['name'],
            'email' => $this->editForm['email'],
            'phone' => $this->editForm['phone'],
            'role' => $this->editForm['role'],
            'status' => $this->editForm['status'],
        ]);

        $user->email_verified_at = $this->editForm['email_verified'] ? ($user->email_verified_at ?? now()) : null;
        $user->phone_verified_at = $this->editForm['phone_verified'] ? ($user->phone_verified_at ?? now()) : null;
        $user->save();

        $user->refresh();
        $this->editForm['email_verified'] = (bool) $user->email_verified_at;
        $this->editForm['phone_verified'] = (bool) $user->phone_verified_at;
        $this->selectedUserMetrics = $this->buildMetrics($user);

        session()->flash('message', 'User details updated.');
        $this->dispatch('user-message');
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
        ]);
    }

    protected function applyFilters(Builder $query): Builder
    {
        if ($this->search !== '') {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function (Builder $inner) use ($searchTerm) {
                $inner->where('name', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('phone', 'like', $searchTerm)
                    ->orWhere('search_id', 'like', $searchTerm);
            });
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
            ->select(['paid', 'amount', 'activity'])
            ->first();

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
            'post' => $post,
            'is_online' => $lastLogin ? $lastLogin->greaterThanOrEqualTo(now()->subMinutes(10)) : false,
        ];
    }
}

<div>
    <div class="container-fluid p-0">
        <div class="row mb-xl-3 mb-2 align-items-center">
            <div class="col">
                <h3 class="fw-semibold mb-0">
                    <strong>Registered</strong> Users
                </h3>
                <p class="text-muted mb-0 small">Monitor account health, activity, and roles in real time.</p>
            </div>
            <div class="col-auto text-end">
                <button class="btn btn-primary" type="button" wire:click="addUser">
                    <i class="align-middle me-1" data-feather="user-plus"></i>
                    Add New User
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Total users</p>
                        <h4 class="fw-semibold mb-0">{{ number_format($summary['total']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Active</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="fw-semibold mb-0 text-success">{{ number_format($summary['active']) }}</h4>
                            <span class="badge bg-success-subtle text-success">Live</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Suspended</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="fw-semibold mb-0 text-danger">{{ number_format($summary['suspended']) }}</h4>
                            <span class="badge bg-danger-subtle text-danger">Needs review</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase fw-medium fs-12 mb-1">New this week</p>
                        <h4 class="fw-semibold mb-0">{{ number_format($summary['newThisWeek']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header border-0 bg-light">
                <div id="userMessage">
                    @if (session('message'))
                        <div class="alert alert-success mb-0 py-2 small">{{ session('message') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mb-0 py-2 small">{{ session('error') }}</div>
                    @endif
                </div>

                <div class="row gy-2 gx-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i data-feather="search" class="icon"></i></span>
                            <input type="search" class="form-control" placeholder="Search name, email, phone, ID" wire:model.debounce.400ms="search">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" wire:model="roleFilter">
                            <option value="all">All roles</option>
                            <option value="donor">Donors</option>
                            <option value="receiver">Receivers</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" wire:model="statusFilter">
                            <option value="all">All statuses</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" wire:model="perPage">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4">#</th>
                                <th scope="col" role="button" wire:click="sortBy('name')" class="text-nowrap">
                                    Name
                                    @if ($sortField === 'name')
                                        <i data-feather="chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}" class="icon-sm"></i>
                                    @endif
                                </th>
                                <th scope="col" role="button" wire:click="sortBy('email')" class="text-nowrap">
                                    Email
                                    @if ($sortField === 'email')
                                        <i data-feather="chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}" class="icon-sm"></i>
                                    @endif
                                </th>
                                <th scope="col">Role</th>
                                <th scope="col" role="button" wire:click="sortBy('status')" class="text-nowrap">
                                    Status
                                    @if ($sortField === 'status')
                                        <i data-feather="chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}" class="icon-sm"></i>
                                    @endif
                                </th>
                                <th scope="col" role="button" wire:click="sortBy('last_login_at')" class="text-nowrap">Last login
                                    @if ($sortField === 'last_login_at')
                                        <i data-feather="chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}" class="icon-sm"></i>
                                    @endif
                                </th>
                                <th scope="col" role="button" wire:click="sortBy('created_at')" class="text-nowrap">Joined
                                    @if ($sortField === 'created_at')
                                        <i data-feather="chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}" class="icon-sm"></i>
                                    @endif
                                </th>
                                <th scope="col" class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $lastLogin = $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at) : null;
                                    $activeNow = $lastLogin ? $lastLogin->greaterThanOrEqualTo(now()->subMinutes(10)) : false;
                                @endphp
                                <tr wire:key="user-{{ $user->id }}" class="align-middle">
                                    <td class="ps-4">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center text-primary fw-semibold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $user->name }}</div>
                                                <div class="text-muted small">ID: {{ $user->search_id ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i data-feather="mail" class="icon-sm text-muted"></i>
                                            <span>{{ $user->email ?? '—' }}</span>
                                        </div>
                                        @if ($user->phone)
                                            <div class="text-muted small d-flex align-items-center gap-2">
                                                <i data-feather="phone" class="icon-sm"></i>
                                                {{ $user->phone }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-capitalize">
                                        <span class="badge bg-info-subtle text-info">{{ $user->role }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $user->status === 'suspended' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                            {{ ucfirst($user->status ?? 'active') }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($lastLogin)
                                            <span>{{ $lastLogin->diffForHumans() }}</span>
                                            @if ($activeNow)
                                                <span class="badge bg-success ms-2">Online</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $user->created_at?->format('M d, Y') ?? '—' }}</td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-outline-primary" wire:click="selectUser({{ $user->id }})">
                                            Manage
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No users found for your current filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer border-0 bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing
                    <strong>{{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }}</strong>
                    of <strong>{{ $users->total() }}</strong> users</small>
                {{ $users->links() }}
            </div>
        </div>
    </div>

    @if ($showUserDrawer && $selectedUserId)
        <div class="offcanvas-backdrop fade show" wire:click="clearSelection"></div>
        <div class="offcanvas offcanvas-end show" tabindex="-1" style="visibility: visible; width: 460px;">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title">Manage user</h5>
                <button type="button" class="btn-close" aria-label="Close" wire:click="clearSelection"></button>
            </div>
            <div class="offcanvas-body">
                <form wire:submit.prevent="saveUser" class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label">Full name</label>
                        <input type="text" class="form-control @error('editForm.name') is-invalid @enderror" wire:model.defer="editForm.name">
                        @error('editForm.name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control @error('editForm.email') is-invalid @enderror" wire:model.defer="editForm.email">
                        @error('editForm.email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="emailVerified" wire:model.defer="editForm.email_verified">
                            <label class="form-check-label small" for="emailVerified">Email verified</label>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control @error('editForm.phone') is-invalid @enderror" wire:model.defer="editForm.phone">
                        @error('editForm.phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="phoneVerified" wire:model.defer="editForm.phone_verified">
                            <label class="form-check-label small" for="phoneVerified">Phone verified</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select class="form-select" wire:model.defer="editForm.role">
                                <option value="donor">Donor</option>
                                <option value="receiver">Receiver</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" wire:model.defer="editForm.status">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>

                    @if (($editForm['role'] ?? null) === 'receiver')
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-semibold mb-0">Recipient profile</h6>
                            <span class="badge bg-info-subtle text-info">Visible to donors</span>
                        </div>
                        <p class="text-muted small">Update location, story, and hardship tags so this receiver appears in donor searches.</p>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                <input
                                    type="text"
                                    class="form-control @error('editForm.country') is-invalid @enderror"
                                    placeholder="Search country"
                                    list="admin-country-options"
                                    wire:model.live="countryInput"
                                >
                                <datalist id="admin-country-options">
                                    @foreach ($countryOptions as $option)
                                        <option value="{{ $option['name'] }}">{{ $option['name'] }}</option>
                                    @endforeach
                                </datalist>
                                <div class="form-text">
                                    @if ($editForm['country_code'] ?? false)
                                        Matched code: {{ $editForm['country_code'] }}
                                    @else
                                        Type to search or enter a custom country.
                                    @endif
                                </div>
                                @error('editForm.country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Region / State</label>
                                <input
                                    type="text"
                                    class="form-control @error('editForm.region') is-invalid @enderror"
                                    placeholder="{{ ($editForm['country_code'] ?? '') ? 'Search state or enter manually' : 'Select a country first' }}"
                                    list="admin-state-options"
                                    {{ ($editForm['country_code'] ?? '') === '' ? 'disabled' : '' }}
                                    wire:model.live="stateInput"
                                >
                                <datalist id="admin-state-options">
                                    @foreach ($stateOptions as $option)
                                        <option value="{{ $option['name'] }}">{{ $option['name'] }}</option>
                                    @endforeach
                                </datalist>
                                <div class="form-text">
                                    @if (($editForm['country_code'] ?? '') === '')
                                        Choose a country to load its regions.
                                    @elseif (($editForm['state_code'] ?? '') !== '')
                                        Matched code: {{ $editForm['state_code'] }}
                                    @elseif (empty($stateOptions))
                                        No catalogued regions for this country; manual entry retained.
                                    @else
                                        Start typing to search available regions.
                                    @endif
                                </div>
                                @error('editForm.region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input
                                    type="text"
                                    class="form-control @error('editForm.city') is-invalid @enderror"
                                    placeholder="{{ ($editForm['state_code'] ?? '') ? 'Search city or enter manually' : 'Select a region or type freely' }}"
                                    list="admin-city-options"
                                    wire:model.live="cityInput"
                                >
                                <datalist id="admin-city-options">
                                    @foreach ($citySuggestions as $option)
                                        <option value="{{ $option['name'] }}">{{ $option['name'] }}</option>
                                    @endforeach
                                </datalist>
                                <div class="form-text">
                                    @if (!empty($citySuggestions) && ($editForm['state_code'] ?? '') !== '')
                                        Showing up to {{ count($citySuggestions) }} suggestions.
                                    @elseif (($editForm['state_code'] ?? '') !== '')
                                        Start typing to see matching cities.
                                    @else
                                        Enter a city name (suggestions appear after selecting a region).
                                    @endif
                                </div>
                                @error('editForm.city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="form-label mt-3">Profile headline</label>
                            <textarea class="form-control @error('editForm.profile_description') is-invalid @enderror" rows="2" placeholder="Short introduction" wire:model.defer="editForm.profile_description"></textarea>
                            @error('editForm.profile_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">Funding goal (optional)</label>
                                <input type="text" class="form-control @error('editForm.post_amount') is-invalid @enderror" placeholder="e.g. 250" wire:model.defer="editForm.post_amount">
                                @error('editForm.post_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Story visible to donors</label>
                                <textarea class="form-control @error('editForm.post_biography') is-invalid @enderror" rows="3" placeholder="Why are funds needed?" wire:model.defer="editForm.post_biography"></textarea>
                                @error('editForm.post_biography')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Hardship tags</label>
                            <select class="form-select @error('editForm.hardship_ids') is-invalid @enderror" multiple size="4" wire:model.defer="editForm.hardship_ids">
                                @forelse ($availableTags as $tag)
                                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                @empty
                                    <option disabled>No tags available</option>
                                @endforelse
                            </select>
                            <div class="form-text">These tags help donors find relevant recipients.</div>
                            @error('editForm.hardship_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @php
                        $userPost = $selectedUserMetrics['post'] ?? null;
                    @endphp
                    <div class="border rounded-3 p-3 bg-light">
                        <h6 class="fw-semibold mb-2">Activity snapshot</h6>
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted">Account ID</dt>
                            <dd class="col-7">{{ $selectedUserMetrics['search_id'] ?? '—' }}</dd>

                            <dt class="col-5 text-muted">Registered</dt>
                            <dd class="col-7">{{ optional($selectedUserMetrics['registered_at'])->format('M d, Y H:i') ?? '—' }}</dd>

                            <dt class="col-5 text-muted">Last login</dt>
                            <dd class="col-7">
                                @if (!empty($selectedUserMetrics['last_login_at']))
                                    {{ \Carbon\Carbon::parse($selectedUserMetrics['last_login_at'])->diffForHumans() }}
                                    @if ($selectedUserMetrics['is_online'] ?? false)
                                        <span class="badge bg-success ms-2">Online</span>
                                    @endif
                                @else
                                    Never
                                @endif
                            </dd>

                            <dt class="col-5 text-muted">Donations made</dt>
                            <dd class="col-7">
                                {{ number_format($selectedUserMetrics['donations_count'] ?? 0) }}
                                <span class="text-muted">(USD {{ number_format($selectedUserMetrics['donations_total'] ?? 0, 2) }})</span>
                            </dd>

                            <dt class="col-5 text-muted">Location</dt>
                            <dd class="col-7">{{ $selectedUserMetrics['location'] ?? '—' }}</dd>

                            @if (!empty($selectedUserMetrics['profile_description']))
                                <dt class="col-5 text-muted">Profile intro</dt>
                                <dd class="col-7">{{ \Illuminate\Support\Str::limit($selectedUserMetrics['profile_description'], 120) }}</dd>
                            @endif

                            <dt class="col-5 text-muted">Receiver status</dt>
                            <dd class="col-7">
                                @if (optional($userPost)->activity === 1)
                                    Active request (goal {{ optional($userPost)->amount ?? '—' }}, paid {{ optional($userPost)->paid ?? '0' }})
                                @elseif ($userPost)
                                    Inactive request
                                @else
                                    Not requesting assistance
                                @endif
                            </dd>

                            @if (!empty($userPost['biography']))
                                <dt class="col-5 text-muted">Story</dt>
                                <dd class="col-7">{{ \Illuminate\Support\Str::limit($userPost['biography'], 120) }}</dd>
                            @endif

                            @if (!empty($userPost['tags']))
                                <dt class="col-5 text-muted">Hardships</dt>
                                <dd class="col-7">{{ implode(', ', $userPost['tags']) }}</dd>
                            @endif
                        </dl>
                    </div>

                    <div class="d-flex gap-2 mt-1">
                        <button type="submit" class="btn btn-primary flex-fill">Save changes</button>
                        <button type="button" class="btn btn-outline-secondary" wire:click="clearSelection">Close</button>
                    </div>
                    <button type="button" class="btn btn-outline-danger w-100" wire:click="toggleStatus">
                        {{ ($editForm['status'] ?? 'active') === 'suspended' ? 'Reactivate user' : 'Suspend user' }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

@push('admin-script')
    <script>
        document.addEventListener('livewire:load', () => {
            document.addEventListener('user-message', () => {
                const message = document.querySelector('#userMessage');
                if (!message) return;

                setTimeout(() => {
                    message.innerHTML = '';
                }, 2800);
            });
        });
    </script>
@endpush

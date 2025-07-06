<div class="sidebar-user">
    <div class="d-flex justify-content-center">
        <div class="flex-shrink-0">
            @if (!is_null(request()->user()->avatar))
                <img class="avatar img-fluid me-1 rounded" src="{{ '/storage' }}/{{ request()->user()->avatar }}"
                    alt="{{ request()->user()->name }}" />
            @else
                <img class="avatar img-fluid me-1 rounded" src="{{ asset('panel/img/user.png') }}" alt="{{ request()->user()->name }}" />
            @endif

        </div>
        <div class="flex-grow-1 ps-2">
            <p class="sidebar-user-title mb-0">{{ request()->user()->name }}</p>
            <p class="sidebar-user-subtitle">{{ request()->user()->role }}</p>
        </div>
    </div>
</div>

<ul class="sidebar-nav">
    <li class="sidebar-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
        <a class='sidebar-link' href='{{ route('admin.dashboard') }}'>
            <i class="align-middle" data-feather="codesandbox"></i> <span class="align-middle">Dasboard</span>
        </a>
    </li>

    <li class="sidebar-header">
        Sections
    </li>

    <li class="sidebar-item {{ Route::is('admin.tag') ? 'active' : '' }}">
        <a class='sidebar-link' href='{{ route('admin.tag') }}'>
            <i class="align-middle" data-feather="tag"></i> <span class="align-middle">Tags</span>
        </a>
    </li>

    <li class="sidebar-item {{ Route::is('admin.faq') ? 'active' : '' }}">
        <a class='sidebar-link' href='{{ route('admin.faq') }}'>
            <i class="align-middle" data-feather="help-circle"></i> <span class="align-middle">FAQs</span>
        </a>
    </li>

    <li class="sidebar-item {{ Route::is('admin.post') ? 'active' : '' }}">
        <a class='sidebar-link' href='{{ route('admin.post') }}'>
            <i class="align-middle" data-feather="align-left"></i> <span class="align-middle">Posts</span>
        </a>
    </li>

    <li class="sidebar-item {{ Route::is('admin.withdraw') ? 'active' : '' }}">
        <a class='sidebar-link' href='{{ route('admin.withdraw') }}'>
            <i class="align-middle" data-feather="dollar-sign"></i> <span class="align-middle">Withdraws</span>
        </a>
    </li>

    <li class="sidebar-item {{ Route::is('admin.setting') ? 'active' : '' }}">
        <a class='sidebar-link' href='{{ route('admin.setting') }}'>
            <i class="align-middle" data-feather="settings"></i> <span class="align-middle">Settings</span>
        </a>
    </li>

    <li class="sidebar-header">
        Management
    </li>

    <li class="sidebar-item {{ Route::is('admin.user') ? 'active' : '' }}">
        <a class='sidebar-link' href='{{ route('admin.user') }}'>
            <i class="align-middle" data-feather="users"></i> <span class="align-middle">Users</span>
        </a>
    </li>
</ul>

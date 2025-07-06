<div>
    <div class="container-fluid p-0">

        <div class="row mb-xl-3 mb-2">
            <div class="d-none d-sm-block col-auto">
                <h3><strong>Registered</strong> Users</h3>
            </div>

            <div class="mt-n1 col-auto ms-auto text-end">
                <button class="btn btn-primary" type="button" wire:click="addUser">Add New User</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div id='userMessage'>
                    @if (session('message'))
                        <h6 class="card-subtitle text-success fw-bolder">{{ session('message') }}</h6>
                    @endif
                    @if (session('error'))
                        <h6 class="card-subtitle text-danger fw-bolder">{{ session('error') }}</h6>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <table class="table" style="width:100%">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Gender</th>
                            <th class="text-center">Verified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr wire:key='{{ $user->id }}'>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    @if (!is_null($user->avatar))
                                        <img class="rounded-circle me-2" src="{{ '/storage' }}/{{ $user->avatar }}"
                                            alt="{{ $user->name }}" width="40" height="40" />
                                    @else
                                        <img class="rounded-circle me-2" src="{{ asset('images/user.png') }}"
                                            alt="{{ $user->name }}" width="40" height="40" />
                                    @endif

                                    {{ $user->name }}
                                </td>

                                <td>{{ $user->email }}</td>

                                <td>{{ $user->gender }}</td>

                                <td
                                    class="{{ !is_null($user->email_verified_at) ? 'text-success' : 'text-secondary' }} text-center">
                                    {{ !is_null($user->email_verified_at) ? 'Yes' : 'No' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

@push('admin-script')
    <script>
        // session message hide
        let userMessage = document.querySelector('#userMessage');
        document.addEventListener('user-message', (e) => {
            setTimeout(() => {
                userMessage.style.display = 'none';
            }, 2500);
        });
    </script>
@endpush

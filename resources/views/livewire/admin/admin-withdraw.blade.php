<div>
    <div class="container-fluid p-0">
        <div class="row mb-xl-3 mb-2">
            <div class="d-none d-sm-block col-auto">
                <h3><strong>Donation</strong> Withdraws</h3>
            </div>
        </div>

        <div class="card">
            <div class="card-header pb-0">
                <div id='sessionMessage'>
                    @if (session('success'))
                        <h6 class="card-subtitle text-success fw-bolder">{{ session('success') }}</h6>
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
                            <th>Receiver Name</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($withdraws as $withdraw)
                            <tr wire:key='{{ $withdraw->id }}'>

                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $withdraw->user->name }}</td>

                                <td>$ {{ $withdraw->amount }}</td>

                                <td>{{ Illuminate\Support\Carbon::parse($withdraw->created_at)->format('jS M, Y') }}</td>

                                <td>
                                    <div class="btn-group">
                                        <button
                                            class="btn {{ $withdraw->status == 1 ? 'btn-success' : ($withdraw->status == 2 ? 'btn-danger' : 'btn-warning') }} dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button" aria-haspopup="true" aria-expanded="false">
                                            {{ $withdraw->status == 1 ? 'Approved' : ($withdraw->status == 2 ? 'Rejected' : 'Pending') }}
                                        </button>
                                        @if ($withdraw->status == 0)
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" wire:click.prevent="approve({{ $withdraw->id }})">Approve</a>
                                                <a class="dropdown-item" wire:click.prevent="reject({{ $withdraw->id }})">Reject</a>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    @if (!is_null($withdraw->user->bankDetail))
                                        <div class="btn-group">
                                            <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" type="button"
                                                aria-haspopup="true" aria-expanded="false">
                                                Bank Details
                                            </button>
                                            <div class="dropdown-menu" style="width: 260px;">
                                                <div class="ps-2">
                                                    <p class="mb-1">Bank Name: <span
                                                            class="text-info">{{ optional($withdraw->user->bankDetail)->bank_name }}</span>
                                                    </p>
                                                    <p class="mb-1">Account No: <span
                                                            class="text-info">{{ optional($withdraw->user->bankDetail)->account_no }}</span>
                                                    </p>
                                                    <p class="mb-1">Account Name: <span
                                                            class="text-info">{{ optional($withdraw->user->bankDetail)->account_name }}</span>
                                                    </p>
                                                    <p class="mb-1">IFSC no: <span
                                                            class="text-info">{{ optional($withdraw->user->bankDetail)->ifsc_code }}</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $withdraws->links() }}
            </div>

        </div>
    </div>
</div>

@push('admin-script')
    <script>
        // session message hide
        let sessionMessage = document.querySelector('#sessionMessage');
        document.addEventListener('session-message', (e) => {
            setTimeout(() => {
                sessionMessage.style.display = 'none';
            }, 2000);
        });
    </script>
@endpush

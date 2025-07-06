<div>
    <div class="container-fluid p-0">
        <div class="row mb-xl-3 mb-2">
            <div class="d-none d-sm-block col-auto">
                <h3><strong>Receiver</strong> Posts</h3>
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
                            <th>Image</th>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            <tr wire:key='{{ $post->id }}'>

                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <img class="me-2" src="{{ '/storage' }}/{{ $post->image }}" width="60" height="80" />
                                </td>

                                <td>
                                    {{ Str::limit($post->biography, 80) }}
                                </td>

                                <td>$ {{ $post->amount }}</td>

                                <td>{{ Illuminate\Support\Carbon::parse($post->created_at)->format('jS M, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $posts->links() }}
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

        let showPostModal = new bootstrap.Modal(document.querySelector('#showPostModal'), {});

        document.addEventListener('post-show', () => {
            showPostModal.show();
        })
    </script>
@endpush

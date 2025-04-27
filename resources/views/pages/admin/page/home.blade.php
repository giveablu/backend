@extends('layouts.admin')
@section('admin-section')
    <main class="content">
        <div>
            <div class="container-fluid p-0">

                <div class="row mb-xl-3 mb-2">
                    <div class="d-none d-sm-block col-auto">
                        <h3><strong>Home</strong> Page</h3>
                    </div>
                </div>

                <livewire:admin.page.home.admin-home-slider>

                    <livewire:admin.page.home.admin-home-welcome>

                        <livewire:admin.page.home.admin-home-parallex>

            </div>
        </div>
    </main>
@endsection

@push('admin-script')
    <script>
        // session message hide
        let sessionMessage = document.querySelector('#sessionMessage');
        document.addEventListener('session-message', (e) => {
            setTimeout(() => {
                sessionMessage.style.display = 'none';
            }, 2500);
        });
    </script>
@endpush

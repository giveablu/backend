@extends('layouts.auth')
@section('auth-section')
    <div class="d-table-cell align-middle">

        <div class="mt-4 text-center">
            <h1 class="h2">Verify Your Email</h1>
            <p class="lead">
                Verify your account to continue
            </p>
            <p class="text-primary fw-bold text-center" id="message"></p>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="m-sm-3">
                    <div class="d-none">
                        <form id="changeForm">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input class="form-control form-control-lg" name="email" type="email" placeholder="Enter your email" />
                                <small class="text-danger" id="email_error"></small>
                            </div>
    
                            <button class="btn btn-primary" type="submit">Change Email</button>
    
                        </form>
                    </div>

                    <div class="row mt-3">
                        <div class="col">
                            <hr>
                        </div>
                        <div class="text-uppercase d-flex align-items-center col-auto">Email not received?</div>
                        <div class="col">
                            <hr>
                        </div>

                        <div class="mt-3 text-center">
                            <form id="resendForm">
                                <button class="btn btn-sm btn-light">Click Here Send Again</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    @push('auth-script')
        <script>
            var resendForm = document.querySelector('#resendForm');
            var csrf_token = document.querySelector('[name="csrf_token"]')
            var message = document.querySelector('#message');

            resendForm.addEventListener('submit', function(e) {
                e.preventDefault();
                axios.post('{{route('verification.send')}}', {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf_token.content,
                        },
                    })
                    .then(function(res) {
                        message.style.display = 'block';
                        message.innerText = res.data.message;
                        setTimeout(() => {
                            if (res.data.url) {
                                window.location.replace(res.data.url)
                            }
                        }, 2000);
                    })
                    .catch(function(error) {
                        console.log(error);
                    })
            });
        </script>
    @endpush
@endsection

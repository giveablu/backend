@extends('layouts.auth')
@section('auth-section')
    <div class="d-table-cell align-middle">

        <div class="mt-4 text-center">
            <h1 class="h2">Forgot password</h1>
            <p class="lead">
                Enter your email to reset your password.
            </p>
            <p class="text-primary fw-bold text-center" id="message"></p>
            <h6 class="text-warning fw-bold d-none text-center" id="waitMessage">Please Wait...</h6>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="m-sm-3">
                    <form id="forgotPassForm">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input class="form-control form-control-lg" name="email" type="email" placeholder="Enter your email" />
                            <small class="text-danger" id="email_error"></small>
                        </div>
                        <div class="d-grid mt-3 gap-2">
                            <button class='btn btn-lg btn-primary' type="submit">Reset password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="mb-3 text-center">
            Remember Password? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>

    @push('auth-script')
        <script>
            var forgotPassForm = document.querySelector('#forgotPassForm');
            var email_input = document.querySelector('[name="email"]');
            var email_error = document.querySelector('#email_error');
            var csrf_token = document.querySelector('[name="csrf_token"]')
            var message = document.querySelector('#message');
            var waitMessage = document.querySelector('#waitMessage');

            forgotPassForm.addEventListener('submit', function(e) {
                e.preventDefault();
                waitMessage.classList.remove("d-none");

                axios.post('{{ route('password.email') }}', {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf_token.content,
                        },
                        email: email_input.value,
                    })
                    .then(function(res) {
                        waitMessage.classList.add("d-none");
                        email_error.style.display = 'none';
                        message.style.display = 'block';


                        if (res.data.status) {
                            if (res.data.status == 'passwords.sent') {
                                message.innerText = 'A link sent to your email to reset password';
                            } else if (res.data.status == 'passwords.throttled') {
                                message.innerText = 'Too many attempts, try after some time';
                            } else if (res.data.status == 'passwords.user') {
                                message.innerText = 'User Not Found';
                            }

                            setTimeout(() => {
                                message.style.display = 'none';
                            }, 2500);
                        }
                    })
                    .catch(function(error) {
                        waitMessage.classList.add("d-none");
                        if (error.response.data.errors) {
                            error.response.data.errors.email == undefined ? email_error.style.display = 'none' : email_error.style.display =
                                'block';

                            email_error.innerText = error.response.data.errors.email;
                        }

                    })
            });
        </script>
    @endpush
@endsection

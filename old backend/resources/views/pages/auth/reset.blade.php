@extends('layouts.auth')
@section('auth-section')
    <div class="d-table-cell align-middle">

        <div class="mt-4 text-center">
            <h1 class="h2">Reset password</h1>
            <p class="lead">
                Verify Email to Reset Password
            </p>
            <p class="text-primary fw-bold text-center" id="message"></p>
            <h6 class="text-warning fw-bold d-none text-center" id="waitMessage">Please Wait...</h6>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="m-sm-3">
                    <form id="resetPassForm">
                        <input name="token" type="hidden" value="{{ $token }}">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input class="form-control form-control-lg" name="email" type="email" placeholder="Enter Your Email" />
                            <small class="text-danger" id="email_error"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input class="form-control form-control-lg" name="password" type="password" placeholder="Enter new password" />
                            <small class="text-danger" id="password_error"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input class="form-control form-control-lg" name="password_confirmation" type="password" placeholder="Confirm new password" />
                        </div>

                        <div class="d-grid mt-3 gap-2">
                            <button class='btn btn-lg btn-primary' type="submit">Reset password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('auth-script')
        <script>
            var resetPassForm = document.querySelector('#resetPassForm');
            var token_input = document.querySelector('[name="token"]');
            var email_input = document.querySelector('[name="email"]');
            var password_input = document.querySelector('[name="password"]');
            var cpassword_input = document.querySelector('[name="password_confirmation"]');
            var email_error = document.querySelector('#email_error');
            var password_error = document.querySelector('#password_error');
            var csrf_token = document.querySelector('[name="csrf_token"]')
            var message = document.querySelector('#message');
            var waitMessage = document.querySelector('#waitMessage');

            resetPassForm.addEventListener('submit', function(e) {
                e.preventDefault();
                waitMessage.classList.remove("d-none");

                axios.post('{{ route('password.update') }}', {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf_token.content,
                        },
                        token: token_input.value,
                        email: email_input.value,
                        password: password_input.value,
                        password_confirmation: cpassword_input.value,
                    })
                    .then(function(res) {
                        waitMessage.classList.add("d-none");
                        email_error.style.display = 'none';
                        password_error.style.display = 'none';
                        message.style.display = 'block';

                        if (res.data.status) {
                            if (res.data.status == 'passwords.reset') {
                                message.innerText = 'Password Reset Successful, redirecting to login';
                                setTimeout(() => {
                                    window.location.replace(res.data.url)
                                }, 3000);
                            } else if (res.data.status == 'passwords.token') {
                                message.innerText = 'Sorry! Try again later';

                            } else if (res.data.status == 'passwords.user') {
                                message.innerText = 'Provided Credentials are wrong';

                            }
                            setTimeout(() => {
                                message.style.display = 'none';
                            }, 2500);
                        }

                    })
                    .catch(function(error) {
                        waitMessage.classList.add("d-none");
                        if (error.response.data.errors) {
                            error.response.data.errors.email == undefined ? email_error.style.display = 'none' : email_error.style
                                .display =
                                'block';

                            error.response.data.errors.password == undefined ? password_error.style.display = 'none' : password_error.style
                                .display =
                                'block';

                            email_error.innerText = error.response.data.errors.email;
                            password_error.innerText = error.response.data.errors.password;
                        }

                    })
            });
        </script>
    @endpush
@endsection

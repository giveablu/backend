<!DOCTYPE html>
<html>

<head>
    <title>Blucharity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Manjari:wght@100;400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.css ">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.css">

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon.ico') }}">

    <style>
        * {
            padding: 0px;
            margin: 0px;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            line-height: 1.2;
            font-size: 12px;
            background: #f2f2f2;
        }

        .logo img {
            max-width: 100%;
            width: 100px;
        }

        .headerbg {
            background: #fff;
            height: 100px;
        }

        .map {
            width: 500px;
            background: #fff;
            height: 550px;
        }

        .d-flex1 {
            display: flex;
            justify-content: end;

        }

        .error {
            color: red;
        }

        .req {
            color: red;
        }

        .email-contain {
            text-align: left;
            font-size: 20px;
            padding: 20px;
        }

        .contact-text {
            padding-left: 20px;
        }

        .email-contain img {
            padding-right: 15px;
        }

        .email-contain a {
            text-decoration: none;
        }

        .justy-content {
            justify-content: flex-end;
        }
    </style>
</head>

<body id="myDIV">
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <main class="d-flex w-100 h-100">
        <div class="d-flex flex-column container">

            <div class="row vh-100">
                <div class="row headerbg">
                    <div class="col-sm-4 logo">
                        <img src="{{ asset('panel/img/icons/logo.png') }}" alt="" />
                    </div>
                    <div class="col-sm-8">
                        <div class="row ">
                            <div class="col-sm-12 email-contain d-flex justy-content">
                                <div class="contact-text">
                                    <a href="mailto:customer_suport@gmail.com">
                                        <img src="{{ asset('panel/img/icons/email.svg') }}" alt="" /> customer_suport@gmail.com </a>
                                </div>
                                <div class="contact-text">
                                <a href="tel:+919674854892"> <img src="{{ asset('panel/img/icons/phone.svg') }}" alt="" /> 9674854892 </a>
                                </div>
                            </div>
                            <div class="col-sm-6">

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 d-table m-auto align-middle pt-5">
                            <div class="d-table-cell ">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="m-sm-3">
                                            <h1 class="h2 text-center">Contact Us</h1>
                                            <form id="loginForm" action="{{ url('/store') }}" method="post" autocomplete="off" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">Name<span class="req">*</span></label>
                                                    <input class="form-control form-control-lg" name="email" type="email" placeholder="Enter your name" required>
                                                    <small class="text-danger" id="name_error"></small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Email<span class="req">*</span></label>
                                                    <input class="form-control form-control-lg" name="email" type="email" placeholder="Enter your email" required>
                                                    <small class="text-danger" id="email_error"></small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Phone <span class="req">*</span></label>
                                                    <input class="form-control form-control-lg" id="phone_no" name="phone_no" placeholder="Enter Phone number" required>
                                                    <small class="text-danger" id="phone_no_error"></small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Message/Requirements </label>
                                                    <textarea placeholder="Enter Message/Requirements" class="form-control form-control-lg"></textarea>
                                                    <small class="text-danger" id="message_error"></small>
                                                </div>


                                                <div class="text-center">
                                                    <button class="btn btn-primary btn-lg mt-3" type="submit">Save</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
    </main>
    <!-- Scripts moved to the end for performance -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#phone_no').on('input', function() {
                var input = $(this).val();
                var phonePattern = /^\(\d{3}\) \d{3}-\d{4}$/;
                var numericOnly = input.replace(/\D/g, ''); // Extract only numeric characters

                if (numericOnly.length < 10 || numericOnly.length > 12) {
                    $('#phone_no_error').text('Phone number must be between 10 and 12 numeric characters.');
                    $(this).addClass('is-invalid');
                } else {
                    $('#phone_no_error').text('');
                    $(this).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>

</html>
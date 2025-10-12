<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.gstatic.com/" rel="preconnect">
    <link href="{{ asset('panel/img/icons/icon-48x48.png') }}" rel="shortcut icon" />

    <title>{{ config('app.name') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&amp;display=swap" rel="stylesheet">

    <link href="{{ asset('panel/css/light.css') }}" rel="stylesheet">
    @livewireStyles

    <style>
        body {
            opacity: 0;
        }
    </style>
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <div class="wrapper">
        <nav class="sidebar js-sidebar" id="sidebar">
            <div class="sidebar-content js-simplebar">
                <a class='sidebar-brand' href='index.html'>
                    <span class="sidebar-brand-text align-middle">
                        {{ Str::ucfirst(request()->user()->role) }} Account
                    </span>
                    <svg class="sidebar-brand-icon align-middle" style="margin-left: -3px" width="32px" height="32px"
                        viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="square"
                        stroke-linejoin="miter" color="#FFFFFF">
                        <path d="M12 4L20 8.00004L12 12L4 8.00004L12 4Z"></path>
                        <path d="M20 12L12 16L4 12"></path>
                        <path d="M20 16L12 20L4 16"></path>
                    </svg>
                </a>

                @include('partials.admin.sidebar')
            </div>
        </nav>

        <div class="main">
            @include('partials.admin.navbar')

            @yield('admin-section')
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-messaging.js"></script>
    <script src="{{ asset('panel/js/app.js') }}"></script>
    <script src="{{ asset('panel/js/axios.min.js') }}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    @livewireScripts(['asset_url' => asset('vendor/livewire')])
    @stack('admin-script')
    <script>
        // Your web app's Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyBPOMqKKdqSdpPj9tz_afIEJvaqgRXyV_Q",
            authDomain: "galvanic-crow-406709.firebaseapp.com",
            projectId: "galvanic-crow-406709",
            storageBucket: "galvanic-crow-406709.appspot.com",
            messagingSenderId: "178025172773",
            appId: "1:178025172773:web:0178b8a88037c6d3bf94cc",
            measurementId: "G-G271MLZDYP"
        };
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);

        // Retrieve Firebase Messaging object.
        const messaging = firebase.messaging();

        // Get registration token
        messaging.getToken({
            vapidKey: 'BOH7Vmk2aaAxdYC8rgmLiwoR65SoNewHF94mjBsrejwvJ9OhgOqEHnqXOcDQpUlLnbOOvy4n5PQxNjLGMug6jXs'
        }).then((currentToken) => {
            if (currentToken) {
                console.log('Current token:', currentToken);
                // Send the token to your server and save it
                saveToken(currentToken);
            } else {
                console.log('No registration token available. Request permission to generate one.');
            }
        }).catch((err) => {
            console.log('An error occurred while retrieving token. ', err);
        });

        function saveToken(token) {
            fetch('{{ route('admin.save-device-token') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    device_token: token
                })
            }).then(response => {
                return response.json();
            }).then(data => {
                console.log('Token saved:', data);
            }).catch((error) => {
                console.error('Error saving token:', error);
            });

        }
    </script>
</body>

</html>

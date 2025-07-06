<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ config('app.name') }} | API DOC</title>
    <link href="{{ asset('swagger/style.css') }}" rel="stylesheet">
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="{{ asset('swagger/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('swagger/swagger.bundle.js') }}"></script>
    <script type="application/javascript">
        const ui = SwaggerUIBundle({
            url: "{{asset('swagger/swagger.yaml')}}",
            dom_id: '#swagger-ui'
        });
    </script>
</body>

</html>

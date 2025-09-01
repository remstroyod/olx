<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="OLX">
    <meta name="keywords" content="OLX">
    <meta name="author" content="Alex Cherniy">
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">
    <title> @yield('title') | OLX</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Include Styles -->
    @includeIf('partials.global.css')

</head>

<body>

    <div class="container">
        <div class="row justify-content-lg-center">
            @yield('content')
        </div>
    </div>

    <!-- Include Scripts -->
    @includeIf('partials.global.scripts')
</body>
</html>

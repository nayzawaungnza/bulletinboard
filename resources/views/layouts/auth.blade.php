<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .auth-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .auth-header {
            background: transparent;
            border-bottom: none;
            padding: 1.5rem;
            text-align: center;
            font-weight: 600;
            font-size: 1.5rem;
        }
        .auth-btn {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
        }
        .form-control {
            padding: 12px;
            border-radius: 8px;
        }
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light">
    <div class="auth-container">
        <div class="container">
            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
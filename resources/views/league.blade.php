<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }} - Insider Champions League</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Meta tags for better mobile experience -->
    <meta name="description" content="Insider Champions League - Football simulation with Chelsea, Arsenal, Manchester City, and Liverpool">
    <meta name="keywords" content="football, league, simulation, premier league, champions">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <!-- Main Application -->
    <main id="app">
        <league-app></league-app>
    </main>



    <!-- Loading Spinner (hidden by default) -->
    <div id="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading league data...</p>
        </div>
    </div>

    <style>
        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loading-content {
            text-align: center;
        }

        .navbar-brand:hover {
            transform: scale(1.02);
            transition: transform 0.2s;
        }

        main {
            min-height: calc(100vh - 200px);
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>

    <!-- Global JavaScript variables -->
    <script>
        // Make CSRF token available globally for axios
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
        
        // Set up axios defaults
        if (typeof axios !== 'undefined') {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        }
    </script>
</body>
</html> 
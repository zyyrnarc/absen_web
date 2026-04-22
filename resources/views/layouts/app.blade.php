<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    @php
        $hasViteAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
    @endphp

    @if ($hasViteAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        @php
            $fallbackCss = preg_replace('/^@tailwind\s+.+;$/m', '', file_get_contents(resource_path('css/app.css')));
        @endphp
        <style>{!! $fallbackCss !!}</style>
    @endif
</head>
<body class="font-nunito min-h-screen flex">
    @include('layouts.sidebar')
    <main class="flex-1 flex flex-col min-h-screen overflow-auto">
        @include('layouts.header')
        <div class="flex-1 p-6">
            @if(session('success'))
                <div class="flash-success mb-4">{{ session('success') }}</div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>

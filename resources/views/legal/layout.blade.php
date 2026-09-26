<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="max-w-3xl mx-auto px-6 py-12">
        <div class="flex gap-4 text-sm text-gray-500 mb-8">
            <a href="{{ route('legal.terms') }}" class="hover:text-gray-900 {{ request()->routeIs('legal.terms') ? 'font-semibold text-gray-900' : '' }}">Terms of Service</a>
            <a href="{{ route('legal.privacy') }}" class="hover:text-gray-900 {{ request()->routeIs('legal.privacy') ? 'font-semibold text-gray-900' : '' }}">Privacy Policy</a>
            <a href="{{ route('legal.refund-policy') }}" class="hover:text-gray-900 {{ request()->routeIs('legal.refund-policy') ? 'font-semibold text-gray-900' : '' }}">Refund Policy</a>
        </div>

        <h1 class="text-3xl font-display font-bold text-gray-900 mb-2">@yield('title')</h1>
        <p class="text-sm text-gray-500 mb-8">Last updated: {{ now()->format('d M, Y') }}</p>

        <div class="space-y-4 text-sm leading-relaxed [&_h2]:text-lg [&_h2]:font-bold [&_h2]:text-gray-900 [&_h2]:mt-8 [&_h2]:mb-2 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1">
            @yield('content')
        </div>
    </div>
</body>
</html>

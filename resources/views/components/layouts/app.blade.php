<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Rituals') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant:wght@400;500;700&family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @fluxAppearance
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --deep-black: #0f0f0f;
            --warm-text: #e8e4df;
            --muted-text: #9a9590;
            --warm-accent: #d4a574;
            --soft-pink: #e8b4b8;
            --gold: #c9b896;
            --terracotta: #c4856a;
        }

        body {
            font-family: 'Cormorant', serif;
            background-color: var(--deep-black);
            color: var(--warm-text);
            -webkit-font-smoothing: antialiased;
        }

        .font-accent {
            font-family: 'IBM Plex Sans', sans-serif;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="h-full">
    <div class="min-h-[100dvh] flex flex-col">
        <livewire:navigation />

        <main class="flex-grow">
            {{ $slot }}
        </main>

        <footer class="py-8 border-t border-white/10 text-center text-sm text-[#9a9590]">
            <p>&copy; {{ date('Y') }} Rituals Multi-Location. All rights reserved.</p>
        </footer>
    </div>

    @fluxScripts
</body>
</html>

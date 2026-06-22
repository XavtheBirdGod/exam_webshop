<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Rituals')); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant:wght@400;500;700&family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    <?php echo app('flux')->fluxAppearance(); ?>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

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
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('navigation', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-80498480-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>

        <main class="flex-grow">
            <?php echo e($slot); ?>

        </main>

        <footer class="py-8 border-t border-white/10 text-center text-sm text-[#9a9590]">
            <p>&copy; <?php echo e(date('Y')); ?> Rituals Multi-Location. All rights reserved.</p>
        </footer>
    </div>

    <?php app('livewire')->forceAssetInjection(); ?>
<?php echo app('flux')->scripts(); ?>

</body>
</html>
<?php /**PATH C:\wamp64\www\exam_webshop\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>
<?php
use App\Livewire\Forms\LoginForm;
use Livewire\Component;
use Livewire\Attributes\Layout;
?>

<div class="min-h-[80dvh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white/5 p-10 rounded-[2rem] border border-white/10 backdrop-blur-xl">
        <div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-[#d4a574]">Sign in to Rituals</h2>
            <p class="mt-2 text-center text-sm text-[#9a9590]">
                Or <a href="/register" class="font-medium text-[#c9b896] hover:text-[#d4a574]">create a new account</a>
            </p>
        </div>
        <form class="mt-8 space-y-6" wire:submit="login">
            <div class="space-y-4">
                <div>
                    <label for="email-address" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Email address</label>
                    <input wire:model="form.email" id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded-full relative block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="Email address">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label for="password" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Password</label>
                    <input wire:model="form.password" id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-full relative block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="Password">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input wire:model="form.remember" id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-[#d4a574] focus:ring-[#d4a574] border-white/10 rounded bg-[#0f0f0f]">
                    <label for="remember-me" class="ml-2 block text-sm text-[#9a9590] font-accent uppercase tracking-widest">Remember me</label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-[#c9b896] hover:text-[#d4a574] font-accent uppercase tracking-widest">Forgot password?</a>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold rounded-full text-[#0f0f0f] bg-[#d4a574] hover:brightness-110 active:scale-95 transition-all uppercase tracking-widest">
                    Sign in
                </button>
            </div>
        </form>
    </div>
</div><?php /**PATH C:\wamp64\www\exam_webshop\storage\framework\views/livewire/views/207254df.blade.php ENDPATH**/ ?>
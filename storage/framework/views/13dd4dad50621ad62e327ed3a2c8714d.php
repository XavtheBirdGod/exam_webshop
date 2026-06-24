<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
?>

<div class="min-h-[80dvh] py-12 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <div class="bg-white/5 p-10 rounded-[2rem] border border-white/10 backdrop-blur-xl">
        <h2 class="text-3xl font-bold tracking-tight text-[#d4a574] mb-8 text-center">Contact Us</h2>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($success): ?>
            <div class="mb-8 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-center font-accent tracking-wider">
                Thank you for your message! We will get back to you soon.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form wire:submit="submit" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Name</label>
                <input wire:model="name" id="name" type="text" required class="appearance-none rounded-full relative block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="Your name">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <label for="email" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Email address</label>
                <input wire:model="email" id="email" type="email" required class="appearance-none rounded-full relative block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="Email address">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <label for="message" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Message</label>
                <textarea wire:model="message" id="message" rows="5" required class="appearance-none rounded-2xl relative block w-full px-6 py-4 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm" placeholder="How can we help you?"></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold rounded-full text-[#0f0f0f] bg-[#d4a574] hover:brightness-110 active:scale-95 transition-all uppercase tracking-widest">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div><?php /**PATH C:\wamp64\www\exam_webshop\storage\framework\views/livewire/views/37179706.blade.php ENDPATH**/ ?>
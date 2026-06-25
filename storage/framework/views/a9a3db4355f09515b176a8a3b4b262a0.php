<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use App\Services\CartService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
?>

<div class="relative min-h-[85dvh] py-16">
    <div class="absolute inset-0 bg-radial-at-t from-[#d4a574]/5 via-transparent to-transparent opacity-60 pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-6 relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <!-- Checkout Form -->
        <div class="lg:col-span-7">
            <h1 class="text-4xl font-bold mb-8 text-[#e8e4df]">Checkout</h1>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form wire:submit="processCheckout" class="space-y-8">
                
                <!-- Contact Info -->
                <div class="bg-[#161615] rounded-[32px] border border-white/5 p-8">
                    <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Contact Information</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">Email Address</label>
                            <input type="email" wire:model.blur="email" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Please enter a valid email address (e.g., name@example.com)" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none" placeholder="you@example.com" required>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-400 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="bg-[#161615] rounded-[32px] border border-white/5 p-8">
                    <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Shipping Address</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">First Name</label>
                            <input type="text" wire:model="first_name" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-400 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">Last Name</label>
                            <input type="text" wire:model="last_name" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-400 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">Address</label>
                            <input type="text" wire:model="address" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none" placeholder="Street name and house number">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-400 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">City</label>
                            <input type="text" wire:model="city" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-400 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">Postal Code</label>
                            <input type="text" wire:model="zip_code" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['zip_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-400 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-[#161615] rounded-[32px] border border-white/5 p-8">
                    <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Payment Method</h2>
                    <div class="space-y-3">
                        <label class="flex items-center gap-4 p-4 rounded-xl border border-white/10 cursor-pointer hover:bg-white/5 transition-colors <?php echo e($payment_method === 'stripe' ? 'bg-white/5 border-[#d4a574]' : ''); ?>">
                            <input type="radio" wire:model="payment_method" value="stripe" class="text-[#d4a574] focus:ring-[#d4a574] bg-transparent border-white/20">
                            <div class="flex flex-col">
                                <span class="text-[#e8e4df] font-semibold">Stripe Checkout</span>
                                <span class="text-xs text-[#9a9590]">Pay with Credit Card, iDEAL, or PayPal securely via Stripe.</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-4 p-4 rounded-xl border border-white/10 cursor-pointer hover:bg-white/5 transition-colors <?php echo e($payment_method === 'ideal' ? 'bg-white/5 border-[#d4a574]' : ''); ?>">
                            <input type="radio" wire:model="payment_method" value="ideal" class="text-[#d4a574] focus:ring-[#d4a574] bg-transparent border-white/20">
                            <span class="text-[#e8e4df] font-semibold">iDEAL</span>
                        </label>
                        <label class="flex items-center gap-4 p-4 rounded-xl border border-white/10 cursor-pointer hover:bg-white/5 transition-colors <?php echo e($payment_method === 'credit_card' ? 'bg-white/5 border-[#d4a574]' : ''); ?>">
                            <input type="radio" wire:model="payment_method" value="credit_card" class="text-[#d4a574] focus:ring-[#d4a574] bg-transparent border-white/20">
                            <span class="text-[#e8e4df] font-semibold">Credit Card</span>
                        </label>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-400 text-xs mt-2 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button type="submit" class="w-full py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-lg hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="processCheckout">Complete Order</span>
                    <span wire:loading wire:target="processCheckout">Processing...</span>
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-5">
            <div class="bg-[#161615] rounded-[32px] border border-white/5 p-8 sticky top-32">
                <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Order Summary</h2>
                
                <div class="space-y-4 mb-6">
                    <?php $items = app(\App\Services\CartService::class)->getCartDetails(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-xs text-[#d4a574] font-semibold"><?php echo e($item['quantity']); ?></span>
                                <span class="text-[#e8e4df]"><?php echo e($item['name']); ?></span>
                            </div>
                            <span class="text-[#9a9590]">€ <?php echo e(number_format($item['price'] * $item['quantity'], 2, ',', '.')); ?></span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
                
                <div class="border-t border-white/5 pt-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-[#9a9590]">Subtotal</span>
                        <span class="text-[#e8e4df]">€ <?php echo e(number_format(app(\App\Services\CartService::class)->getTotal(), 2, ',', '.')); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#9a9590]">Shipping</span>
                        <span class="text-[#d4a574]">Free</span>
                    </div>
                </div>

                <div class="border-t border-white/5 mt-6 pt-6 flex justify-between items-end">
                    <span class="text-[#e8e4df] font-bold">Total</span>
                    <span class="text-3xl font-bold text-[#e8e4df]">
                        € <?php echo e(number_format(app(\App\Services\CartService::class)->getTotal(), 2, ',', '.')); ?>

                    </span>
                </div>
            </div>
        </div>

    </div>
</div><?php /**PATH C:\wamp64\www\exam_webshop\storage\framework\views/livewire/views/88b03cb7.blade.php ENDPATH**/ ?>
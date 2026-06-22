<div class="relative min-h-[80dvh] py-12 px-6 max-w-7xl mx-auto w-full" wire:poll.10s>
    <!-- Breadcrumbs & Title -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-white/10 pb-8 mb-12">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-[#9a9590]">
                <a href="/seller/dashboard" class="hover:text-[#d4a574]">Dashboard</a>
                <span>/</span>
                <a href="<?php echo e(route('seller.orders.index')); ?>" wire:navigate class="hover:text-[#d4a574]">Orders</a>
                <span>/</span>
                <span class="text-[#e8e4df]">#<?php echo e($order->id); ?></span>
            </div>
            <div class="flex items-center gap-4 mt-2">
                <h1 class="text-4xl font-bold text-[#e8e4df]">Order #<?php echo e($order->id); ?></h1>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'paid'): ?>
                    <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold tracking-wider uppercase">Paid</span>
                <?php elseif($order->status === 'pending'): ?>
                    <span class="px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold tracking-wider uppercase">Pending</span>
                <?php else: ?>
                    <span class="px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-semibold tracking-wider uppercase"><?php echo e($order->status); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <p class="text-sm text-[#9a9590] mt-2">Placed on <?php echo e($order->created_at->format('F j, Y \a\t H:i')); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Line Items -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Line Items</h2>
                <div class="divide-y divide-white/5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="py-4 flex justify-between items-center">
                            <div class="flex gap-4 items-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->productVariant && $item->productVariant->product && $item->productVariant->product->images->count() > 0): ?>
                                    <img src="<?php echo e(Storage::url($item->productVariant->product->images->first()->path)); ?>" class="w-16 h-16 rounded-xl object-cover border border-white/10">
                                <?php else: ?>
                                    <div class="w-16 h-16 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#9a9590]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div>
                                    <p class="font-bold text-[#e8e4df]"><?php echo e($item->name); ?></p>
                                    <p class="text-xs text-[#9a9590] mt-1"><?php echo e($item->quantity); ?> x €<?php echo e(number_format($item->price / 100, 2)); ?></p>
                                </div>
                            </div>
                            <div class="font-mono font-semibold text-[#d4a574]">
                                €<?php echo e(number_format(($item->price * $item->quantity) / 100, 2)); ?>

                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
                <div class="border-t border-white/10 mt-6 pt-6 flex justify-between items-end">
                    <span class="text-[#9a9590] font-semibold">Total Amount</span>
                    <span class="text-3xl font-bold text-[#e8e4df]">
                        €<?php echo e(number_format($order->total_amount / 100, 2)); ?>

                    </span>
                </div>
            </div>

            <!-- Payment Logs -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->paymentLogs->count() > 0): ?>
                <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                    <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Payment History</h2>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->paymentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="p-4 rounded-xl border border-white/5 bg-white/[0.02] flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-semibold text-[#e8e4df] uppercase tracking-wide"><?php echo e($log->provider); ?> - <?php echo e($log->status); ?></p>
                                    <p class="text-xs text-[#9a9590] mt-1 font-mono">TXN: <?php echo e($log->transaction_id ?? 'N/A'); ?></p>
                                    <p class="text-xs text-[#9a9590] mt-1"><?php echo e($log->created_at->format('M d, Y H:i:s')); ?></p>
                                </div>
                                <div class="font-mono text-[#d4a574] font-semibold text-sm">
                                    €<?php echo e(number_format($log->amount / 100, 2)); ?>

                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Customer Details -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h3 class="font-bold text-[#e8e4df] mb-6 text-lg">Customer</h3>
                <p class="text-[#d4a574] font-semibold"><?php echo e($order->shipping_address['first_name'] ?? ''); ?> <?php echo e($order->shipping_address['last_name'] ?? ''); ?></p>
                <a href="mailto:<?php echo e($order->email); ?>" class="text-[#9a9590] text-sm hover:text-[#e8e4df] transition-colors mt-1 inline-block"><?php echo e($order->email); ?></a>
            </div>

            <!-- Shipping Address -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h3 class="font-bold text-[#e8e4df] mb-6 text-lg">Shipping Address</h3>
                <address class="text-[#9a9590] text-sm not-italic space-y-1">
                    <p><?php echo e($order->shipping_address['address'] ?? 'N/A'); ?></p>
                    <p><?php echo e($order->shipping_address['zip_code'] ?? ''); ?> <?php echo e($order->shipping_address['city'] ?? ''); ?></p>
                </address>
            </div>
            
            <!-- Payment Info -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h3 class="font-bold text-[#e8e4df] mb-6 text-lg">Payment Info</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-[#9a9590] uppercase tracking-wider mb-1">Method</p>
                        <p class="text-sm text-[#e8e4df] font-semibold"><?php echo e(ucfirst(str_replace('_', ' ', $order->payment_method))); ?></p>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->transaction_id): ?>
                        <div>
                            <p class="text-xs text-[#9a9590] uppercase tracking-wider mb-1">Transaction ID</p>
                            <p class="text-xs font-mono text-[#c9b896] break-all"><?php echo e($order->transaction_id); ?></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\wamp64\www\exam_webshop\resources\views/components/seller/orders/⚡detail.blade.php ENDPATH**/ ?>
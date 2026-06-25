<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Product;
use App\Models\ProductVariant;
?>

<div class="relative min-h-[85dvh] py-16">
    <div class="absolute inset-0 bg-radial-at-t from-[#d4a574]/5 via-transparent to-transparent opacity-60 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <!-- Back Link -->
        <div class="mb-12">
            <a href="<?php echo e(route('shop.products')); ?>" class="inline-flex items-center gap-2 text-sm font-accent uppercase tracking-widest text-[#9a9590] hover:text-[#d4a574] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Collection
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <!-- Left Column: Media Gallery -->
            <div class="lg:col-span-6 flex flex-col gap-6">
                <!-- Main Image Panel -->
                <div class="relative aspect-square overflow-hidden bg-[#161615] rounded-[32px] border border-white/5 shadow-inner flex items-center justify-center">
                    <img src="<?php echo e($activeImagePath); ?>" alt="<?php echo e($product->name); ?>" class="w-full h-full object-cover">
                </div>


            </div>

            <!-- Right Column: Product Specs & Options -->
            <div class="lg:col-span-6 flex flex-col justify-between min-h-[500px]">
                <div>
                    <!-- Product Category & Tag -->
                    <div class="flex items-center gap-4 mb-4">
                        <span class="inline-block text-xs font-mono uppercase tracking-widest text-[#9a9590]">
                            <?php echo e($product->category?->name ?? 'Collection'); ?>

                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->featured): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-accent uppercase tracking-wider bg-[#d4a574]/10 text-[#d4a574] border border-[#d4a574]/20">
                                Signature Ritual
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- Title -->
                    <h1 class="text-4xl md:text-5xl font-bold mb-6 text-[#e8e4df]">
                        <?php echo e($product->name); ?>

                    </h1>

                    <!-- Description -->
                    <p class="text-base text-[#9a9590] leading-relaxed mb-8 max-w-xl">
                        <?php echo e($product->description); ?>

                    </p>

                    <!-- Variant Choice Selector -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->variants->isNotEmpty()): ?>
                        <div class="mb-8 border-t border-b border-white/5 py-8">
                            <span class="text-xs font-accent uppercase tracking-widest text-[#9a9590] block mb-4">
                                Select Variation
                            </span>
                            <div class="flex flex-wrap gap-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <button 
                                        wire:click="selectVariant(<?php echo e($variant->id); ?>)" 
                                        class="px-5 py-3 rounded-full border text-sm font-semibold transition-all <?php echo e($selectedVariantId === $variant->id ? 'bg-[#d4a574] text-[#0f0f0f] border-[#d4a574] shadow-[0_2px_8px_rgba(212,165,116,0.2)]' : 'border-white/10 text-[#e8e4df] hover:bg-white/5'); ?>"
                                    >
                                        <?php echo e($variant->name); ?>: <?php echo e($variant->value); ?>

                                    </button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Price and Checkout Controls -->
                <div class="mt-8 bg-[#161615] rounded-[32px] border border-white/5 p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <!-- SKU and Availability Info -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedVariant): ?>
                            <div class="mb-4">
                                <span class="text-[10px] font-mono text-[#9a9590] uppercase block mb-1">
                                    SKU: <?php echo e($this->selectedVariant->sku); ?>

                                </span>
                                
                                <!-- Stock Badge -->
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedVariant->stock_available > 5): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#d4a574]/10 text-[#d4a574] border border-[#d4a574]/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#d4a574]"></span>
                                        In Stock
                                    </span>
                                <?php elseif($this->selectedVariant->stock_available > 0): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#c4856a]/10 text-[#c4856a] border border-[#c4856a]/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#c4856a]"></span>
                                        Only <?php echo e($this->selectedVariant->stock_available); ?> left!
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                        Out of Stock
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- Display Price -->
                        <span class="text-[10px] font-mono uppercase text-[#9a9590] block mb-1">Price</span>
                        <span class="text-3xl font-bold text-[#e8e4df]">
                            € <?php echo e(number_format($this->displayPrice, 2, ',', '.')); ?>

                        </span>
                    </div>

                    <!-- Cart Call to Action -->
                    <button 
                        <?php if(!$this->selectedVariant || $this->selectedVariant->stock_available == 0): ?> disabled <?php endif; ?>
                        wire:click="addToCart"
                        class="w-full md:w-auto px-10 py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-lg hover:brightness-110 active:scale-95 transition-all disabled:opacity-40 disabled:pointer-events-none disabled:bg-white/10 disabled:text-[#9a9590]"
                    >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedVariant && $this->selectedVariant->stock_available > 0): ?>
                            Add to Cart
                        <?php else: ?>
                            Out of Stock
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\wamp64\www\exam_webshop\storage\framework\views/livewire/views/54d51c03.blade.php ENDPATH**/ ?>
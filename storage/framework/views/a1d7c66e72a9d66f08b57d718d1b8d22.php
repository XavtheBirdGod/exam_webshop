<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\ProductForm;
use App\Actions\SaveProductAction;
use App\Models\Category;
?>

<div class="relative min-h-[80dvh] py-12 px-6 max-w-5xl mx-auto w-full">
    <!-- Breadcrumbs & Title -->
    <div class="border-b border-white/10 pb-8 mb-12">
        <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-[#9a9590]">
            <a href="/seller/dashboard" class="hover:text-[#d4a574]">Dashboard</a>
            <span>/</span>
            <a href="/seller/products" class="hover:text-[#d4a574]">Products</a>
            <span>/</span>
            <span class="text-[#e8e4df]">Add Product</span>
        </div>
        <h1 class="text-4xl font-bold text-[#e8e4df] mt-2">Add New Product</h1>
    </div>

    <!-- Form -->
    <form wire:submit="save" class="space-y-8">
        <!-- Main Details Section -->
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] space-y-6">
            <h3 class="text-xl font-bold text-[#d4a574] mb-4">Product Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Product Name</label>
                    <input wire:model="form.name" id="name" type="text" required class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Slug (Optional)</label>
                    <input wire:model="form.slug" id="slug" type="text" placeholder="e.g. body-cream-200ml" class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Description</label>
                <textarea wire:model="form.description" id="description" rows="4" class="appearance-none rounded-3xl block w-full px-6 py-4 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm"></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Base Price -->
                <div>
                    <label for="price" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Base Price (€)</label>
                    <input wire:model="form.price" id="price" type="number" step="0.01" required class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Category</label>
                    <select wire:model="form.category_id" id="category" class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                        <option value="">Select Category</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Status</label>
                    <select wire:model="form.status" id="status" class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="draft">Draft</option>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <!-- Featured -->
            <div class="flex items-center">
                <input wire:model="form.featured" id="featured" type="checkbox" value="1" class="h-4 w-4 text-[#d4a574] focus:ring-[#d4a574] border-white/10 rounded bg-[#0f0f0f]">
                <label for="featured" class="ml-2 block text-sm text-[#9a9590] font-accent uppercase tracking-widest">Featured Product</label>
            </div>
        </div>

        <!-- Variants Section -->
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] space-y-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-[#d4a574]">Product Variants</h3>
                <button type="button" wire:click="addVariant" class="px-5 py-2 rounded-full border border-[#c9b896]/30 hover:bg-white/5 font-semibold text-xs text-[#c9b896] uppercase tracking-wider transition-all">
                    + Add Variant
                </button>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($form->variants)): ?>
                <p class="text-[#9a9590] text-sm text-center py-6">Please add at least one variant.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $form->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-[#0f0f0f]/35 p-6 rounded-2xl border border-white/5 relative">
                            <!-- Type (e.g. Size) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Type</label>
                                <input wire:model="form.variants.<?php echo e($index); ?>.name" type="text" placeholder="e.g. Size" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Value (e.g. 200 ml) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Value</label>
                                <input wire:model="form.variants.<?php echo e($index); ?>.value" type="text" placeholder="e.g. 200 ml" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- SKU -->
                            <div class="md:col-span-3">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">SKU</label>
                                <input wire:model="form.variants.<?php echo e($index); ?>.sku" type="text" placeholder="e.g. RIT-AM-001" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Price Modifier -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Price Mod. (€)</label>
                                <input wire:model="form.variants.<?php echo e($index); ?>.price_modifier" type="number" step="0.01" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Initial Stock -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Initial Stock</label>
                                <input wire:model="form.variants.<?php echo e($index); ?>.stock_on_hand" type="number" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Remove Button -->
                            <div class="md:col-span-1 flex justify-center">
                                <button type="button" wire:click="removeVariant(<?php echo e($index); ?>)" class="p-2.5 rounded-full hover:bg-red-500/10 text-[#9a9590] hover:text-red-400 transition-colors" title="Remove Variant">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.variants'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-4 justify-end">
            <a href="/seller/products" class="px-8 py-4 rounded-full border border-white/10 hover:bg-white/5 font-bold text-sm text-[#e8e4df] transition-all">
                Cancel
            </a>
            <button type="submit" class="px-10 py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-sm hover:brightness-110 active:scale-95 transition-all uppercase tracking-widest">
                Create Product
            </button>
        </div>
    </form>
</div><?php /**PATH C:\wamp64\www\exam_webshop\storage\framework\views/livewire/views/a3cebbcd.blade.php ENDPATH**/ ?>
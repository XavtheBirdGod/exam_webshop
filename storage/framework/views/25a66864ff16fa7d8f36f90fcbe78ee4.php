<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Livewire\Forms\ProductForm;
use App\Actions\SaveProductAction;
use App\Models\Product;
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
            <span class="text-[#e8e4df]">Edit Product</span>
        </div>
        <h1 class="text-4xl font-bold text-[#e8e4df] mt-2">Edit Product: <?php echo e($product->name); ?></h1>
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
                    <label for="slug" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Slug</label>
                    <input wire:model="form.slug" id="slug" type="text" required class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
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

        <!-- Product Images Section -->
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] space-y-6">
            <h3 class="text-xl font-bold text-[#d4a574] mb-4">Product Images</h3>

            <!-- Existing saved images -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($form->existingImages)): ?>
                <div>
                    <p class="text-xs font-mono uppercase tracking-wider text-[#9a9590] mb-3">Saved Images</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $form->existingImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="relative group rounded-2xl overflow-hidden border border-white/10 aspect-square">
                                <img src="<?php echo e($img['url']); ?>" alt="Product image" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button"
                                        wire:click="markExistingImageForDeletion(<?php echo e($img['id']); ?>)"
                                        wire:confirm="Remove this image from the product?"
                                        class="p-2 rounded-full bg-red-500/80 hover:bg-red-500 text-white transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($img['is_primary']): ?>
                                    <span class="absolute top-2 left-2 text-[10px] font-mono uppercase tracking-wider bg-[#d4a574] text-[#0f0f0f] px-2 py-0.5 rounded-full">Primary</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Upload area for new images -->
            <div>
                <p class="text-xs font-mono uppercase tracking-wider text-[#9a9590] mb-3">Add More Images</p>
                <div x-data="{ dragging: false }"
                     @dragover.prevent="dragging = true"
                     @dragleave.prevent="dragging = false"
                     @drop.prevent="dragging = false"
                     :class="dragging ? 'border-[#d4a574] bg-[#d4a574]/5' : 'border-white/10 hover:border-white/25'"
                     class="relative border-2 border-dashed rounded-2xl p-8 text-center transition-all cursor-pointer">
                    <input type="file"
                           wire:model="form.images"
                           id="product-images-edit"
                           multiple
                           accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto text-[#9a9590] mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-[#9a9590] text-sm font-accent">Drop images here or <span class="text-[#d4a574] underline">click to browse</span></p>
                    <p class="text-[#555] text-xs mt-1 font-mono">JPEG, PNG, WEBP or GIF — max 2 MB each</p>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-400 mt-1 block font-accent"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Previews of newly staged uploads -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($form->images)): ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $form->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="relative group rounded-2xl overflow-hidden border border-white/10 aspect-square">
                            <img src="<?php echo e($image->temporaryUrl()); ?>" alt="Preview" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button type="button" wire:click="removeNewImage(<?php echo e($index); ?>)"
                                    class="p-2 rounded-full bg-red-500/80 hover:bg-red-500 text-white transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <span class="absolute top-2 left-2 text-[10px] font-mono uppercase tracking-wider bg-white/20 text-white px-2 py-0.5 rounded-full">New</span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div wire:loading wire:target="form.images" class="text-sm text-[#9a9590] font-accent mt-2">
                Uploading...
            </div>
        </div>

        <!-- Variants Section -->
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] space-y-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-[#d4a574]">Product Variants & Stock</h3>
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

                            <!-- Stock On Hand -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Stock Level</label>
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
                Save Changes
            </button>
        </div>
    </form>
</div><?php /**PATH C:\wamp64\www\exam_webshop\storage\framework\views/livewire/views/5b3d86bb.blade.php ENDPATH**/ ?>
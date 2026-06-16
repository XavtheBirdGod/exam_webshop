<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\ProductForm;
use App\Actions\SaveProductAction;
use App\Models\Product;
use App\Models\Category;

new #[Layout('components.layouts.app')] class extends Component
{
    public Product $product;
    public ProductForm $form;

    public function mount(Product $product)
    {
        $this->product = $product->load('variants');
        $this->form->fillFromProduct($this->product);
    }

    public function addVariant()
    {
        $this->form->variants[] = [
            'id' => null,
            'name' => 'Size',
            'value' => '',
            'sku' => '',
            'price_modifier' => 0,
            'stock_on_hand' => 0,
        ];
    }

    public function removeVariant($index)
    {
        unset($this->form->variants[$index]);
        $this->form->variants = array_values($this->form->variants);
    }

    public function save(SaveProductAction $action)
    {
        $this->form->store($action, $this->product);

        session()->flash('message', 'Product successfully updated.');

        return redirect()->to('/seller/products');
    }

    public function with(): array
    {
        return [
            'categories' => Category::all(),
        ];
    }
};
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
        <h1 class="text-4xl font-bold text-[#e8e4df] mt-2">Edit Product: {{ $product->name }}</h1>
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
                    @error('form.name') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Slug</label>
                    <input wire:model="form.slug" id="slug" type="text" required class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                    @error('form.slug') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Description</label>
                <textarea wire:model="form.description" id="description" rows="4" class="appearance-none rounded-3xl block w-full px-6 py-4 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm"></textarea>
                @error('form.description') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Base Price -->
                <div>
                    <label for="price" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Base Price (€)</label>
                    <input wire:model="form.price" id="price" type="number" step="0.01" required class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                    @error('form.price') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Category</label>
                    <select wire:model="form.category_id" id="category" class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('form.category_id') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Status</label>
                    <select wire:model="form.status" id="status" class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="draft">Draft</option>
                    </select>
                    @error('form.status') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
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
                <h3 class="text-xl font-bold text-[#d4a574]">Product Variants & Stock</h3>
                <button type="button" wire:click="addVariant" class="px-5 py-2 rounded-full border border-[#c9b896]/30 hover:bg-white/5 font-semibold text-xs text-[#c9b896] uppercase tracking-wider transition-all">
                    + Add Variant
                </button>
            </div>

            @if(empty($form->variants))
                <p class="text-[#9a9590] text-sm text-center py-6">Please add at least one variant.</p>
            @else
                <div class="space-y-4">
                    @foreach($form->variants as $index => $variant)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-[#0f0f0f]/35 p-6 rounded-2xl border border-white/5 relative">
                            <!-- Type (e.g. Size) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Type</label>
                                <input wire:model="form.variants.{{ $index }}.name" type="text" placeholder="e.g. Size" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Value (e.g. 200 ml) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Value</label>
                                <input wire:model="form.variants.{{ $index }}.value" type="text" placeholder="e.g. 200 ml" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- SKU -->
                            <div class="md:col-span-3">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">SKU</label>
                                <input wire:model="form.variants.{{ $index }}.sku" type="text" placeholder="e.g. RIT-AM-001" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Price Modifier -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Price Mod. (€)</label>
                                <input wire:model="form.variants.{{ $index }}.price_modifier" type="number" step="0.01" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Stock On Hand -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Stock Level</label>
                                <input wire:model="form.variants.{{ $index }}.stock_on_hand" type="number" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Remove Button -->
                            <div class="md:col-span-1 flex justify-center">
                                <button type="button" wire:click="removeVariant({{ $index }})" class="p-2.5 rounded-full hover:bg-red-500/10 text-[#9a9590] hover:text-red-400 transition-colors" title="Remove Variant">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @error('form.variants') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
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
</div>

<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Product;

new #[Layout('components.layouts.app')] class extends Component
{
    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        session()->flash('message', 'Product successfully deleted.');
    }

    public function with(): array
    {
        return [
            'products' => Product::with(['category', 'variants'])->latest()->get(),
        ];
    }
};
?>

<div class="relative min-h-[80dvh] py-12 px-6 max-w-7xl mx-auto w-full">
    <!-- Breadcrumbs & Title -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-white/10 pb-8 mb-12">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-[#9a9590]">
                <a href="/seller/dashboard" class="hover:text-[#d4a574]">Dashboard</a>
                <span>/</span>
                <span class="text-[#e8e4df]">Products</span>
            </div>
            <h1 class="text-4xl font-bold text-[#e8e4df] mt-2">Manage Products</h1>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="/seller/products/create" class="px-6 py-3 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-sm hover:brightness-110 active:scale-95 transition-all">
                Add Product
            </a>
        </div>
    </div>

    <!-- Session Messages -->
    @if(session()->has('message'))
        <div class="mb-8 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-accent">
            {{ session('message') }}
        </div>
    @endif

    <!-- Products List Card -->
    <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
        @if($products->isEmpty())
            <div class="text-center py-16 text-[#9a9590]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-[#c9b896] mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="text-xl font-bold mb-2 text-[#e8e4df]">No Products Registered</h3>
                <p class="text-sm mb-6">Start building your storefront catalog by adding your first ritual.</p>
                <a href="/seller/products/create" class="px-6 py-3 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-sm hover:brightness-110 active:scale-95 transition-all inline-block">
                    Add Product
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 text-xs font-mono text-[#9a9590] uppercase tracking-wider">
                            <th class="py-4">Product Details</th>
                            <th class="py-4">Category</th>
                            <th class="py-4">Base Price</th>
                            <th class="py-4">Status</th>
                            <th class="py-4">Stock (Variants)</th>
                            <th class="py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm text-[#e8e4df]">
                        @foreach($products as $product)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-6 pr-4">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-xl object-cover border border-white/10 bg-white/5">
                                        <div>
                                            <h4 class="font-bold text-base text-[#e8e4df]">{{ $product->name }}</h4>
                                            <p class="text-xs text-[#9a9590] mt-1 truncate max-w-xs">{{ $product->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6">
                                    <span class="px-3 py-1 rounded-full bg-white/5 border border-white/5 text-xs text-[#c9b896]">
                                        {{ $product->category?->name ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="py-6 font-mono text-[#d4a574] font-semibold">
                                    €{{ number_format($product->price / 100, 2) }}
                                </td>
                                <td class="py-6">
                                    @if($product->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="py-6">
                                    <div class="flex flex-col gap-1">
                                        @php
                                            $totalStock = $product->variants->sum('stock_available');
                                        @endphp
                                        <span class="font-semibold {{ $totalStock < 5 ? 'text-[#e8b4b8]' : 'text-[#e8e4df]' }}">
                                            {{ $totalStock }} available
                                        </span>
                                        <span class="text-xs text-[#9a9590]">
                                            Across {{ $product->variants->count() }} variants
                                        </span>
                                    </div>
                                </td>
                                <td class="py-6 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="/seller/products/{{ $product->id }}/edit" class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-[#c9b896] hover:text-[#d4a574] transition-colors" title="Edit Product">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <button wire:click="deleteProduct({{ $product->id }})" wire:confirm="Are you sure you want to delete this product and all its variants?" class="p-2 rounded-lg bg-white/5 hover:bg-red-500/10 text-[#9a9590] hover:text-red-400 transition-colors" title="Delete Product">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

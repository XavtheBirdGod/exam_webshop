<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Product;
use App\Models\ProductVariant;

new #[Layout('components.layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'totalProducts' => Product::count(),
            'lowStockVariants' => ProductVariant::with('product')
                ->where('stock_available', '<', 5)
                ->get(),
        ];
    }
};
?>

<div class="relative min-h-[80dvh] py-12 px-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-white/10 pb-8 mb-12">
        <div>
            <span class="text-xs font-mono uppercase tracking-widest text-[#9a9590]">Vendor Backoffice</span>
            <h1 class="text-4xl font-bold text-[#e8e4df] mt-2">Boutique Dashboard</h1>
        </div>
        <div class="mt-4 md:mt-0 flex gap-4">
            <a href="/" class="px-6 py-3 rounded-full border border-white/10 hover:bg-white/5 font-bold text-sm text-[#e8e4df] transition-all">
                View Storefront
            </a>
            <a href="/seller/products/create" class="px-6 py-3 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-sm hover:brightness-110 active:scale-95 transition-all">
                Add Product
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <!-- Products Stat Card -->
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] flex items-center justify-between">
            <div>
                <p class="text-sm font-accent uppercase tracking-wider text-[#9a9590] mb-2">Total Products</p>
                <h3 class="text-5xl font-bold text-[#e8e4df]">{{ $totalProducts }}</h3>
            </div>
            <div class="w-16 h-16 rounded-full bg-[#d4a574]/10 flex items-center justify-center text-[#d4a574]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>

        <!-- Low Stock Alerts Card -->
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] flex items-center justify-between">
            <div>
                <p class="text-sm font-accent uppercase tracking-wider text-[#9a9590] mb-2">Low Stock Alerts</p>
                <h3 class="text-5xl font-bold text-[#e8b4b8]">{{ $lowStockVariants->count() }}</h3>
            </div>
            <div class="w-16 h-16 rounded-full bg-[#e8b4b8]/10 flex items-center justify-center text-[#e8b4b8]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Low Stock Table Section -->
    <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
        <h2 class="text-2xl font-bold mb-6 text-[#e8e4df]">Low Stock Warnings</h2>
        
        @if($lowStockVariants->isEmpty())
            <div class="text-center py-12 text-[#9a9590]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#c9b896] mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>All variants have sufficient stock levels.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 text-xs font-mono text-[#9a9590] uppercase tracking-wider">
                            <th class="py-4">Product</th>
                            <th class="py-4">Variant</th>
                            <th class="py-4">SKU</th>
                            <th class="py-4">On Hand</th>
                            <th class="py-4 text-right">Available</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm text-[#e8e4df]">
                        @foreach($lowStockVariants as $variant)
                            <tr>
                                <td class="py-4 font-bold">{{ $variant->product->name }}</td>
                                <td class="py-4">{{ $variant->name }}: {{ $variant->value }}</td>
                                <td class="py-4 font-mono text-xs">{{ $variant->sku }}</td>
                                <td class="py-4">{{ $variant->stock_on_hand }}</td>
                                <td class="py-4 text-right font-bold {{ $variant->stock_available === 0 ? 'text-[#e8b4b8]' : 'text-[#d4a574]' }}">
                                    {{ $variant->stock_available }} left
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Quick Navigation Link Card -->
    <div class="mt-8 flex justify-center gap-8">
        <a href="/seller/products" class="inline-flex items-center gap-2 text-[#d4a574] hover:text-[#c9b896] font-bold text-sm tracking-wider uppercase font-accent transition-colors">
            <span>Manage Products</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </a>
        <a href="{{ route('seller.orders.index') }}" wire:navigate class="inline-flex items-center gap-2 text-[#d4a574] hover:text-[#c9b896] font-bold text-sm tracking-wider uppercase font-accent transition-colors">
            <span>Manage Orders</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </a>
    </div>
</div>

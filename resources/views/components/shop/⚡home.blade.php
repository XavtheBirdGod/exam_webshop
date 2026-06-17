<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Product;

new #[Layout('components.layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'featuredProducts' => Product::where('status', 'active')
                ->where('featured', true)
                ->with(['category', 'variants', 'images'])
                ->take(3)
                ->get(),
        ];
    }

    public function getCentralLocationsUrl(): string
    {
        $port = request()->getPort();
        $scheme = request()->getScheme();
        $host = 'localhost';
        
        if ($port && !in_array($port, [80, 443])) {
            return "{$scheme}://{$host}:{$port}/#locations";
        }
        return "{$scheme}://{$host}/#locations";
    }
};
?>

<div>
    <!-- Hero Section -->
    <div class="relative min-h-[80dvh] flex items-center justify-center overflow-hidden">
        <!-- Decorative Gradient -->
        <div class="absolute inset-0 bg-radial-at-t from-[#d4a574]/10 to-transparent opacity-50"></div>
        
        <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
            <span class="inline-block mb-6 text-sm font-accent uppercase tracking-[0.3em] text-[#c9b896]">Welcome to the Art of Living</span>
            <h1 class="text-6xl md:text-8xl font-bold mb-8 leading-tight">Enrich Your Daily Rituals</h1>
            <p class="text-xl md:text-2xl text-[#9a9590] mb-12 max-w-2xl mx-auto leading-relaxed">
                Discover a collection designed to soothe the soul and transform your home into a sanctuary.
            </p>
            <div class="flex flex-col md:flex-row gap-6 justify-center items-center">
                <a href="{{ route('shop.products') }}" class="inline-flex items-center justify-center px-10 py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-lg hover:brightness-110 active:scale-95 transition-all">
                    Shop Collection
                </a>
                <a href="{{ $this->getCentralLocationsUrl() }}" class="inline-flex items-center justify-center px-10 py-4 rounded-full border border-white/10 hover:bg-white/5 font-bold text-lg text-[#e8e4df] active:scale-95 transition-all">
                    Find a Store
                </a>
            </div>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full border border-[#d4a574]/10 blur-3xl"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full border border-[#c9b896]/10 blur-3xl"></div>
    </div>

    <!-- Featured Products Section -->
    @if ($featuredProducts->isNotEmpty())
        <div id="collection" class="relative z-10 max-w-7xl mx-auto px-6 py-24 border-t border-white/5 scroll-mt-24">
            <div class="mb-16 text-center">
                <span class="inline-block mb-3 text-sm font-accent uppercase tracking-[0.2em] text-[#c9b896]">
                    Curated Collection
                </span>
                <h2 class="text-4xl md:text-5xl font-bold mb-4 text-[#e8e4df]">
                    Featured Rituals
                </h2>
                <p class="text-base text-[#9a9590] max-w-xl mx-auto leading-relaxed">
                    Explore our location's signature products, crafted to bring harmony and mindful luxury into your daily routines.
                </p>
            </div>

            <!-- Asymmetric Grid of Featured Products -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-stretch">
                @foreach($featuredProducts as $index => $product)
                    @php
                        if ($index === 0) {
                            $colSpan = 'md:col-span-7';
                            $borderColor = 'border-[#c9b896]/20';
                        } elseif ($index === 1) {
                            $colSpan = 'md:col-span-5';
                            $borderColor = 'border-[#e8b4b8]/20';
                        } else {
                            $colSpan = 'md:col-span-12';
                            $borderColor = 'border-[#d4a574]/20';
                        }
                    @endphp

                    <div class="{{ $colSpan }} relative overflow-hidden bg-[#161615] rounded-[32px] border {{ $borderColor }} p-8 md:p-12 shadow-[0_2px_12px_rgba(0,0,0,0.06)] flex flex-col justify-between group hover:border-[#d4a574]/40 transition-all duration-300 transform hover:-translate-y-1 min-h-[440px]">
                        <!-- Background Image with Overlay -->
                        <div class="absolute inset-0 z-0">
                            <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover opacity-20 group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0f0f0f] via-[#0f0f0f]/80 to-transparent"></div>
                        </div>

                        <!-- Card Content -->
                        <div class="relative z-10 flex flex-col h-full justify-between flex-grow">
                            <div>
                                <span class="inline-block mb-3 text-xs font-mono uppercase tracking-widest text-[#9a9590]">
                                    {{ $product->category?->name ?? 'Collection' }}
                                </span>
                                <h3 class="text-3xl md:text-4xl font-bold mb-4 text-[#e8e4df] group-hover:text-[#d4a574] transition-colors">
                                    {{ $product->name }}
                                </h3>
                                <p class="text-base text-[#9a9590] mb-8 max-w-md leading-relaxed line-clamp-3">
                                    {{ $product->description }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-4 mt-auto">
                                <div>
                                    <span class="text-xs font-mono uppercase text-[#9a9590] block mb-1">Price</span>
                                    <span class="text-2xl font-bold text-[#e8e4df]">
                                        € {{ number_format($product->price / 100, 2, ',', '.') }}
                                    </span>
                                </div>
                                <a href="{{ route('shop.product-detail', $product) }}" class="inline-flex items-center justify-center px-8 py-3 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-sm hover:brightness-110 active:scale-95 transition-all">
                                    Discover Ritual
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

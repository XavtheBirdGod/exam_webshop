<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;

new #[Layout('components.layouts.app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'c')]
    public ?int $selectedCategory = null;

    public function selectCategory(?int $id): void
    {
        $this->selectedCategory = $id;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $categories = Category::withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        $query = Product::where('status', 'active')
            ->with(['category', 'variants', 'images']);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        return [
            'products' => $query->paginate(12),
            'categories' => $categories,
            'totalProductCount' => Product::where('status', 'active')->count(),
        ];
    }
};
?>

<div class="relative min-h-[85dvh] py-16">
    <!-- Background Accents -->
    <div class="absolute inset-0 bg-radial-at-t from-[#d4a574]/5 via-transparent to-transparent opacity-60 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <!-- Catalog Header -->
        <div class="max-w-3xl mb-16">
            <span class="inline-block mb-3 text-sm font-accent uppercase tracking-[0.2em] text-[#c9b896]">
                Our Collections
            </span>
            <h1 class="text-5xl md:text-6xl font-bold mb-4 text-[#e8e4df]">
                The Rituals Collection
            </h1>
            <p class="text-lg text-[#9a9590] leading-relaxed">
                Browse our complete selection of luxury bath, body, and home products. Filter by category or search below to find your signature scent.
            </p>
        </div>

        <!-- Filters and Search Toolbar -->
        <div class="flex flex-col md:flex-row gap-6 justify-between items-stretch md:items-center mb-12 border-b border-white/5 pb-8">
            <!-- Category Tabs -->
            <div class="flex flex-wrap gap-2 items-center">
                <button 
                    wire:click="selectCategory(null)" 
                    class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all {{ is_null($selectedCategory) ? 'bg-[#d4a574] text-[#0f0f0f]' : 'border border-white/10 text-[#e8e4df] hover:bg-white/5' }}"
                >
                    All ({{ $totalProductCount }})
                </button>

                @foreach($categories as $category)
                    <button 
                        wire:click="selectCategory({{ $category->id }})" 
                        class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all {{ $selectedCategory === $category->id ? 'bg-[#d4a574] text-[#0f0f0f]' : 'border border-white/10 text-[#e8e4df] hover:bg-white/5' }}"
                    >
                        {{ $category->name }} ({{ $category->products_count }})
                    </button>
                @endforeach
            </div>

            <!-- Search Field -->
            <div class="relative w-full md:max-w-md">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#9a9590]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Search catalog..." 
                    class="w-full pl-12 pr-4 py-3 bg-[#161615] rounded-full border border-white/10 text-[#e8e4df] placeholder-[#9a9590] focus:outline-none focus:border-[#d4a574] focus:ring-2 focus:ring-[#d4a574] focus:ring-offset-2 focus:ring-offset-[#0f0f0f] transition-all text-sm"
                />
            </div>
        </div>

        <!-- Products List / Grid -->
        @if($products->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-stretch">
                @foreach($products as $index => $product)
                    @php
                        // Asymmetric layout logic for catalog list: 4-col, 8-col, 8-col, 4-col pattern
                        $cycle = $index % 4;
                        if ($cycle === 0) {
                            $colSpan = 'md:col-span-4';
                            $borderColor = 'border-white/5';
                        } elseif ($cycle === 1) {
                            $colSpan = 'md:col-span-8';
                            $borderColor = 'border-[#c9b896]/20';
                        } elseif ($cycle === 2) {
                            $colSpan = 'md:col-span-8';
                            $borderColor = 'border-[#e8b4b8]/20';
                        } else {
                            $colSpan = 'md:col-span-4';
                            $borderColor = 'border-white/5';
                        }
                    @endphp

                    <div class="{{ $colSpan }} relative overflow-hidden bg-[#161615] rounded-[32px] border {{ $borderColor }} p-6 md:p-8 flex flex-col justify-between group hover:border-[#d4a574]/40 transition-all duration-300 transform hover:-translate-y-1 min-h-[360px]">
                        <!-- Product Background Cover -->
                        <div class="absolute inset-0 z-0">
                            <img src="{{ $product->getImageUrl() }}" alt="" class="w-full h-full object-cover opacity-10 group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0f0f0f] via-[#0f0f0f]/90 to-transparent"></div>
                        </div>

                        <!-- Product Content Info -->
                        <div class="relative z-10 flex flex-col h-full justify-between flex-grow">
                            <div>
                                <div class="flex justify-between items-start gap-4 mb-4">
                                    <span class="inline-block text-xs font-mono uppercase tracking-widest text-[#9a9590]">
                                        {{ $product->category?->name ?? 'Collection' }}
                                    </span>
                                    @if ($product->featured)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-accent uppercase tracking-wider bg-[#d4a574]/10 text-[#d4a574] border border-[#d4a574]/20">
                                            Signature
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-2xl md:text-3xl font-bold mb-3 text-[#e8e4df] group-hover:text-[#d4a574] transition-colors">
                                    {{ $product->name }}
                                </h3>
                                <p class="text-sm text-[#9a9590] mb-6 leading-relaxed line-clamp-2">
                                    {{ $product->description }}
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-4 mt-auto">
                                <div>
                                    <span class="text-[10px] font-mono uppercase text-[#9a9590] block">Starting from</span>
                                    <span class="text-xl font-bold text-[#e8e4df]">
                                        € {{ number_format($product->price / 100, 2, ',', '.') }}
                                    </span>
                                </div>
                                <a href="{{ route('shop.product-detail', $product) }}" class="inline-flex items-center justify-center px-6 py-2.5 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-xs hover:brightness-110 active:scale-95 transition-all">
                                    Discover
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Links -->
            <div class="mt-16">
                {{ $products->links(data: ['scrollTo' => false]) }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-24 bg-[#161615] rounded-[32px] border border-white/5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-[#c9b896] mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="text-2xl font-bold mb-3 text-[#e8e4df]">No Products Found</h3>
                <p class="text-[#9a9590] text-base max-w-md mx-auto mb-8 leading-relaxed">
                    We couldn't find any products matching your filters. Try selecting a different category or refining your search.
                </p>
                <button 
                    wire:click="selectCategory(null)" 
                    class="px-8 py-3 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-sm hover:brightness-110 active:scale-95 transition-all"
                >
                    Clear Filters
                </button>
            </div>
        @endif
    </div>
</div>

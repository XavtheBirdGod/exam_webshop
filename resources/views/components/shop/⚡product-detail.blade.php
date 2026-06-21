<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Product;
use App\Models\ProductVariant;

new #[Layout('components.layouts.app')] class extends Component
{
    public Product $product;
    public ?int $selectedVariantId = null;
    public string $activeImagePath = '';

    public function mount(string $slug): void
    {
        $this->product = Product::with(['category', 'variants', 'images'])->where('slug', $slug)->firstOrFail();
        
        $firstVariant = $this->product->variants->first();
        if ($firstVariant) {
            $this->selectedVariantId = $firstVariant->id;
        }

        $this->activeImagePath = $this->product->getImageUrl();
    }

    public function selectVariant(int $id): void
    {
        $this->selectedVariantId = $id;
    }

    public function selectImage(string $path): void
    {
        $this->activeImagePath = $path;
    }

    #[Computed]
    public function selectedVariant(): ?ProductVariant
    {
        $variant = $this->product->variants->firstWhere('id', $this->selectedVariantId);
        
        // If there are no variants, mock one for the hardcoded item
        if (!$variant) {
            $variant = new ProductVariant();
            $variant->sku = 'TEMP-01';
            $variant->price_modifier = 0;
        }

        return $variant;
    }

    public function addToCart(\App\Services\CartService $cartService): void
    {
        $variant = $this->selectedVariant;
        if (!$variant) return;

        try {
            // Use 999 if it's our mock variant
            $id = $variant->id ?? 999;
            $cartService->add($id, 1);
            
            $this->dispatch('cart-updated');
            
            // Flux UI Toast if available, or session flash
            try {
                \Flux::toast('Added to cart');
            } catch (\Throwable $e) {
                session()->flash('message', 'Added to cart');
            }
        } catch (\Exception $e) {
            try {
                \Flux::toast($e->getMessage(), variant: 'danger');
            } catch (\Throwable $e2) {
                session()->flash('error', $e->getMessage());
            }
        }
    }

    #[Computed]
    public function displayPrice(): float
    {
        $basePrice = $this->product->price;
        $modifier = 0;
        
        $variant = $this->selectedVariant;
        if ($variant) {
            $modifier = $variant->price_modifier;
        }

        return ($basePrice + $modifier) / 100;
    }
};
?>

<div class="relative min-h-[85dvh] py-16">
    <div class="absolute inset-0 bg-radial-at-t from-[#d4a574]/5 via-transparent to-transparent opacity-60 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <!-- Back Link -->
        <div class="mb-12">
            <a href="{{ route('shop.products') }}" class="inline-flex items-center gap-2 text-sm font-accent uppercase tracking-widest text-[#9a9590] hover:text-[#d4a574] transition-colors">
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
                    <img src="{{ $activeImagePath }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                </div>

                <!-- Gallery Thumbnails -->
                @if($product->images->isNotEmpty())
                    <div class="flex gap-4 overflow-x-auto pb-2">
                        <!-- Add fallback/primary default thumbnail first if relevant -->
                        @foreach($product->images as $image)
                            <button 
                                wire:click="selectImage('{{ $image->path }}')" 
                                class="w-20 h-20 rounded-2xl overflow-hidden border transition-all {{ $activeImagePath === $image->path ? 'border-[#d4a574]' : 'border-white/5 opacity-60 hover:opacity-100' }}"
                            >
                                <img src="{{ $image->path }}" alt="Thumbnail" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right Column: Product Specs & Options -->
            <div class="lg:col-span-6 flex flex-col justify-between min-h-[500px]">
                <div>
                    <!-- Product Category & Tag -->
                    <div class="flex items-center gap-4 mb-4">
                        <span class="inline-block text-xs font-mono uppercase tracking-widest text-[#9a9590]">
                            {{ $product->category?->name ?? 'Collection' }}
                        </span>
                        @if ($product->featured)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-accent uppercase tracking-wider bg-[#d4a574]/10 text-[#d4a574] border border-[#d4a574]/20">
                                Signature Ritual
                            </span>
                        @endif
                    </div>

                    <!-- Title -->
                    <h1 class="text-4xl md:text-5xl font-bold mb-6 text-[#e8e4df]">
                        {{ $product->name }}
                    </h1>

                    <!-- Description -->
                    <p class="text-base text-[#9a9590] leading-relaxed mb-8 max-w-xl">
                        {{ $product->description }}
                    </p>

                    <!-- Variant Choice Selector -->
                    @if($product->variants->isNotEmpty())
                        <div class="mb-8 border-t border-b border-white/5 py-8">
                            <span class="text-xs font-accent uppercase tracking-widest text-[#9a9590] block mb-4">
                                Select Variation
                            </span>
                            <div class="flex flex-wrap gap-3">
                                @foreach($product->variants as $variant)
                                    <button 
                                        wire:click="selectVariant({{ $variant->id }})" 
                                        class="px-5 py-3 rounded-full border text-sm font-semibold transition-all {{ $selectedVariantId === $variant->id ? 'bg-[#d4a574] text-[#0f0f0f] border-[#d4a574] shadow-[0_2px_8px_rgba(212,165,116,0.2)]' : 'border-white/10 text-[#e8e4df] hover:bg-white/5' }}"
                                    >
                                        {{ $variant->name }}: {{ $variant->value }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Price and Checkout Controls -->
                <div class="mt-8 bg-[#161615] rounded-[32px] border border-white/5 p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <!-- SKU and Availability Info -->
                        @if($this->selectedVariant)
                            <div class="mb-4">
                                <span class="text-[10px] font-mono text-[#9a9590] uppercase block mb-1">
                                    SKU: {{ $this->selectedVariant->sku }}
                                </span>
                                
                                <!-- Stock Badge -->
                                @if($this->selectedVariant->stock_available > 5)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#d4a574]/10 text-[#d4a574] border border-[#d4a574]/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#d4a574]"></span>
                                        In Stock
                                    </span>
                                @elseif($this->selectedVariant->stock_available > 0)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#c4856a]/10 text-[#c4856a] border border-[#c4856a]/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#c4856a]"></span>
                                        Only {{ $this->selectedVariant->stock_available }} left!
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                        Out of Stock
                                    </span>
                                @endif
                            </div>
                        @endif

                        <!-- Display Price -->
                        <span class="text-[10px] font-mono uppercase text-[#9a9590] block mb-1">Price</span>
                        <span class="text-3xl font-bold text-[#e8e4df]">
                            € {{ number_format($this->displayPrice, 2, ',', '.') }}
                        </span>
                    </div>

                    <!-- Cart Call to Action -->
                    <button 
                        @if(!$this->selectedVariant || $this->selectedVariant->stock_available == 0) disabled @endif
                        wire:click="addToCart"
                        class="w-full md:w-auto px-10 py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-lg hover:brightness-110 active:scale-95 transition-all disabled:opacity-40 disabled:pointer-events-none disabled:bg-white/10 disabled:text-[#9a9590]"
                    >
                        @if($this->selectedVariant && $this->selectedVariant->stock_available > 0)
                            Add to Cart
                        @else
                            Out of Stock
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Services\CartService;

new #[Layout('components.layouts.app')] class extends Component
{
    public function updateQuantity(CartService $cartService, int $variantId, int $quantity)
    {
        try {
            $cartService->updateQuantity($variantId, $quantity);
            $this->dispatch('cart-updated');
        } catch (\Exception $e) {
            try {
                \Flux::toast($e->getMessage(), variant: 'danger');
            } catch (\Throwable $e2) {
                session()->flash('error', $e->getMessage());
            }
        }
    }

    public function removeItem(CartService $cartService, int $variantId)
    {
        $cartService->remove($variantId);
        $this->dispatch('cart-updated');
    }

    #[Computed]
    public function cartItems()
    {
        return app(CartService::class)->getCartDetails();
    }

    #[Computed]
    public function cartTotal()
    {
        return app(CartService::class)->getTotal();
    }
};
?>

<div class="relative min-h-[85dvh] py-16">
    <div class="absolute inset-0 bg-radial-at-t from-[#d4a574]/5 via-transparent to-transparent opacity-60 pointer-events-none"></div>

    <div class="max-w-4xl mx-auto px-6 relative z-10">
        <h1 class="text-4xl font-bold mb-8 text-[#e8e4df]">Shopping Cart</h1>

        @if(session()->has('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if(empty($this->cartItems))
            <div class="bg-[#161615] rounded-[32px] border border-white/5 p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-white/10 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h2 class="text-2xl font-semibold text-[#e8e4df] mb-4">Your cart is empty</h2>
                <p class="text-[#9a9590] mb-8">Looks like you haven't added anything to your cart yet.</p>
                <a href="{{ route('shop.products') }}" wire:navigate class="inline-flex px-8 py-3 rounded-full bg-[#d4a574] text-[#0f0f0f] font-semibold hover:brightness-110 active:scale-95 transition-all">
                    Continue Shopping
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 mb-12">
                @foreach($this->cartItems as $item)
                    <div class="bg-[#161615] rounded-[24px] border border-white/5 p-6 flex flex-col md:flex-row items-start md:items-center gap-6">
                        <!-- Image -->
                        <div class="w-24 h-24 rounded-2xl overflow-hidden bg-white/5 flex-shrink-0">
                            @if($item['image'])
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Details -->
                        <div class="flex-grow">
                            <h3 class="text-lg font-bold text-[#e8e4df] mb-1">{{ $item['name'] }}</h3>
                            <p class="text-sm text-[#9a9590] mb-3">{{ $item['variant_name'] }}: {{ $item['variant_value'] }}</p>
                            
                            @if($item['stock_available'] <= 5)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-[#c4856a]/10 text-[#c4856a] border border-[#c4856a]/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#c4856a]"></span>
                                    Only {{ $item['stock_available'] }} left!
                                </span>
                            @endif
                        </div>

                        <!-- Quantity Controls -->
                        <div class="flex items-center gap-4">
                            <div class="flex items-center bg-white/5 rounded-full border border-white/10 overflow-hidden">
                                <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})" class="w-10 h-10 flex items-center justify-center text-[#e8e4df] hover:bg-white/10 transition-colors">
                                    -
                                </button>
                                <span class="w-8 text-center text-sm font-semibold text-[#e8e4df]">
                                    {{ $item['quantity'] }}
                                </span>
                                <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})" class="w-10 h-10 flex items-center justify-center text-[#e8e4df] hover:bg-white/10 transition-colors">
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Price and Remove -->
                        <div class="flex flex-col items-end gap-2 md:min-w-[120px]">
                            <span class="text-xl font-bold text-[#e8e4df]">
                                € {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}
                            </span>
                            <button wire:click="removeItem({{ $item['id'] }})" class="text-xs font-semibold text-red-400 hover:text-red-300 transition-colors uppercase tracking-wider">
                                Remove
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Cart Summary -->
            <div class="bg-[#161615] rounded-[32px] border border-white/5 p-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <span class="text-sm font-mono uppercase text-[#9a9590] block mb-1">Total Amount</span>
                    <span class="text-3xl font-bold text-[#e8e4df]">
                        € {{ number_format($this->cartTotal, 2, ',', '.') }}
                    </span>
                </div>
                
                <a href="{{ route('shop.checkout.index') }}" wire:navigate class="w-full md:w-auto px-10 py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-lg hover:brightness-110 active:scale-95 transition-all inline-block text-center">
                    Proceed to Checkout
                </a>
            </div>
        @endif
    </div>
</div>

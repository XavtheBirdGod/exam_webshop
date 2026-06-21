<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;

new #[Layout('components.layouts.app')] class extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        $this->order = $order->load('items');
    }
};
?>

<div class="relative min-h-[85dvh] py-16 flex items-center justify-center">
    <div class="absolute inset-0 bg-radial-at-t from-green-500/5 via-transparent to-transparent opacity-60 pointer-events-none"></div>

    <div class="max-w-2xl mx-auto px-6 relative z-10 w-full">
        <div class="bg-[#161615] rounded-[32px] border border-white/5 p-12 text-center">
            
            <div class="w-20 h-20 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-8 border border-green-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-[#e8e4df] mb-4">Thank you for your order!</h1>
            <p class="text-[#9a9590] mb-8">We've received your order and are getting it ready. A confirmation email has been sent to <span class="text-[#e8e4df]">{{ $order->email }}</span>.</p>
            
            <div class="bg-white/5 rounded-2xl p-6 text-left mb-8 border border-white/5">
                <div class="flex justify-between items-center mb-6 border-b border-white/5 pb-4">
                    <span class="text-[#9a9590]">Order number</span>
                    <span class="text-[#e8e4df] font-mono font-bold">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-[#e8e4df]">{{ $item->quantity }}x {{ $item->name }}</span>
                            <span class="text-[#9a9590]">€ {{ number_format($item->price / 100, 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center mt-6 border-t border-white/5 pt-4 font-bold">
                    <span class="text-[#e8e4df]">Total paid</span>
                    <span class="text-[#d4a574]">€ {{ number_format($order->total_amount / 100, 2, ',', '.') }}</span>
                </div>
            </div>

            <a href="{{ route('shop.home') }}" wire:navigate class="inline-flex px-8 py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold hover:brightness-110 active:scale-95 transition-all">
                Continue Shopping
            </a>
        </div>
    </div>
</div>

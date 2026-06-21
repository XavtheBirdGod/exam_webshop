<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component
{
};
?>

<div class="relative min-h-[85dvh] py-16 flex items-center justify-center">
    <div class="absolute inset-0 bg-radial-at-t from-red-500/5 via-transparent to-transparent opacity-60 pointer-events-none"></div>

    <div class="max-w-2xl mx-auto px-6 relative z-10 w-full">
        <div class="bg-[#161615] rounded-[32px] border border-white/5 p-12 text-center">
            
            <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-8 border border-red-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-[#e8e4df] mb-4">Payment Cancelled</h1>
            <p class="text-[#9a9590] mb-8">Your checkout process was cancelled. No charges have been made.</p>
            
            <div class="flex gap-4 justify-center">
                <a href="{{ route('shop.cart') }}" wire:navigate class="px-8 py-4 rounded-full border border-white/10 text-[#e8e4df] font-bold hover:bg-white/5 active:scale-95 transition-all">
                    Return to Cart
                </a>
                <a href="{{ route('shop.checkout.index') }}" wire:navigate class="px-8 py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold hover:brightness-110 active:scale-95 transition-all">
                    Try Again
                </a>
            </div>
        </div>
    </div>
</div>

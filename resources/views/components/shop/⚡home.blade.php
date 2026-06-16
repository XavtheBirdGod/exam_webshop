<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component
{
    //
};
?>

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
            <button class="px-10 py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-lg hover:brightness-110 active:scale-95 transition-all">
                Shop Collection
            </button>
            <button class="px-10 py-4 rounded-full border border-white/10 hover:bg-white/5 font-bold text-lg active:scale-95 transition-all">
                Find a Store
            </button>
        </div>
    </div>

    <!-- Decorative Elements -->
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full border border-[#d4a574]/10 blur-3xl"></div>
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full border border-[#c9b896]/10 blur-3xl"></div>
</div>

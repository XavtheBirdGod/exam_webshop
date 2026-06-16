<?php

use Livewire\Component;

new class extends Component
{
    public $shopName;

    public function mount()
    {
        $this->shopName = tenant('id') 
            ? ucfirst(str_replace(['shop-', 'rituals-'], '', tenant('id'))) 
            : 'Rituals';
    }
};
?>

<nav class="sticky top-0 z-[100] bg-[#0f0f0f]/80 backdrop-blur-md border-b border-white/5 py-4">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="/" class="text-2xl font-bold tracking-tight text-[#d4a574]">RITUALS</a>
            <span class="text-xs font-accent uppercase tracking-widest text-[#9a9590] border-l border-white/10 pl-4">
                {{ $this->shopName }}
            </span>
        </div>
        
        <div class="flex gap-8 items-center text-sm font-accent uppercase tracking-widest text-[#9a9590]">
            <a href="#" class="hover:text-[#d4a574] transition-colors">Products</a>
            <a href="#" class="hover:text-[#d4a574] transition-colors">Locations</a>
            <a href="#" class="hover:text-[#d4a574] transition-colors">About</a>
        </div>

        <div class="flex gap-4 items-center text-[#e8e4df]">
            <button class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </button>
            <button class="px-6 py-2 rounded-full bg-[#d4a574] text-[#0f0f0f] font-semibold text-sm hover:brightness-110 active:scale-95 transition-all">
                Cart (0)
            </button>
        </div>
    </div>
</nav>

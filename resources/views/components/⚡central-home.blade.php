<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant;

new #[Layout('components.layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'tenants' => Tenant::with('domains')->get(),
        ];
    }

    public function getTenantUrl($domain): string
    {
        $port = request()->getPort();
        $scheme = request()->getScheme();
        
        if ($port && !in_array($port, [80, 443])) {
            return "{$scheme}://{$domain}:{$port}";
        }
        
        return "{$scheme}://{$domain}";
    }
};
?>

<div class="relative min-h-[85dvh] flex flex-col justify-center overflow-hidden py-16">
    <!-- Decorative Radial Gradient -->
    <div class="absolute inset-0 bg-radial-at-t from-[#d4a574]/15 via-transparent to-transparent opacity-60"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-6 w-full">
        <!-- Hero Header -->
        <div class="max-w-3xl mb-16 animate-fade-in">
            <span class="inline-block mb-4 text-sm font-accent uppercase tracking-[0.3em] text-[#c9b896]">
                Rituals Multi-Location Platform
            </span>
            <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight tracking-tight text-[#e8e4df]">
                The Art of <span class="text-[#d4a574]">Soulful</span> Living
            </h1>
            <p class="text-lg md:text-xl text-[#9a9590] leading-relaxed max-w-2xl">
                Welcome to our central gateway. Choose one of our signature physical locations below to explore its local boutique, check live product availability, and order your favorite body and home rituals.
            </p>
        </div>

        <!-- Asymmetric Location Cards Grid -->
        <div id="locations" class="grid grid-cols-1 md:grid-cols-12 gap-8 items-stretch scroll-mt-24">
            @forelse($tenants as $index => $tenant)
                @php
                    $domain = $tenant->domains->first()?->domain;
                    $url = $domain ? $this->getTenantUrl($domain) : '#';
                    $isEven = $index % 2 === 0;
                    
                    // Asymmetric grid column spans based on index
                    if ($index === 0) {
                        $colSpan = 'md:col-span-7';
                        $illustrationColor = 'text-[#c9b896]/10';
                        $borderColor = 'border-[#c9b896]/20';
                    } elseif ($index === 1) {
                        $colSpan = 'md:col-span-5';
                        $illustrationColor = 'text-[#e8b4b8]/10';
                        $borderColor = 'border-[#e8b4b8]/20';
                    } else {
                        $colSpan = 'md:col-span-12';
                        $illustrationColor = 'text-[#d4a574]/10';
                        $borderColor = 'border-[#d4a574]/20';
                    }
                @endphp

                <div class="{{ $colSpan }} relative overflow-hidden bg-[#161615] rounded-[32px] border {{ $borderColor }} p-8 md:p-12 shadow-[0_2px_12px_rgba(0,0,0,0.06)] flex flex-col justify-between group hover:border-[#d4a574]/40 transition-all duration-300 transform hover:-translate-y-1">
                    <!-- Botanical Background Line-Art SVG (Illustrative) -->
                    <div class="absolute right-[-10%] bottom-[-10%] w-64 h-64 pointer-events-none transition-transform duration-700 group-hover:scale-110 {{ $illustrationColor }}">
                        @if ($index === 0)
                            <!-- Lotus Flower Illustration -->
                            <svg viewBox="0 0 100 100" fill="currentColor">
                                <path d="M50 15 C40 35, 20 45, 50 85 C80 45, 60 35, 50 15 Z" />
                                <path d="M50 35 C35 50, 25 60, 50 85 C75 60, 65 50, 50 35 Z" opacity="0.7"/>
                                <path d="M50 50 C40 60, 35 68, 50 85 C65 68, 60 60, 50 50 Z" opacity="0.5"/>
                            </svg>
                        @elseif ($index === 1)
                            <!-- Cherry Blossom Illustration -->
                            <svg viewBox="0 0 100 100" fill="currentColor">
                                <circle cx="50" cy="50" r="15" />
                                <circle cx="50" cy="20" r="10" />
                                <circle cx="50" cy="80" r="10" />
                                <circle cx="20" cy="50" r="10" />
                                <circle cx="80" cy="50" r="10" />
                                <path d="M50 20 Q50 50 80 50 Q50 50 50 80 Q50 50 20 50 Q50 50 50 20 Z" opacity="0.5"/>
                            </svg>
                        @else
                            <!-- Abstract Branch / Leaf -->
                            <svg viewBox="0 0 100 100" fill="currentColor">
                                <path d="M50 90 Q30 50 50 10 Q70 50 50 90 Z" />
                                <path d="M50 90 Q15 60 40 40" stroke="currentColor" stroke-width="2" fill="none" />
                                <path d="M50 90 Q85 60 60 40" stroke="currentColor" stroke-width="2" fill="none" />
                            </svg>
                        @endif
                    </div>

                    <div>
                        <span class="inline-block mb-3 text-xs font-mono uppercase tracking-widest text-[#9a9590]">
                            Boutique Location {{ $index + 1 }}
                        </span>
                        <h2 class="text-3xl md:text-4xl font-bold mb-4 text-[#e8e4df] group-hover:text-[#d4a574] transition-colors">
                            Rituals {{ ucfirst(str_replace(['shop-', 'rituals-'], '', $tenant->id)) }}
                        </h2>
                        <p class="text-base text-[#9a9590] mb-8 max-w-md leading-relaxed">
                            Discover localized inventories, custom pricing, and same-day boutique pick-up options curated exclusively for this store.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 z-10">
                        <a href="{{ $url }}" class="inline-flex items-center justify-center px-8 py-3 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-sm hover:brightness-110 active:scale-95 transition-all">
                            Visit Storefront
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-12 text-center py-16 bg-[#161615] rounded-[32px] border border-white/5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#c9b896] mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-xl font-bold mb-2">No Locations Found</h3>
                    <p class="text-[#9a9590] text-sm">Please register new shop tenants via the platform admin dashboard.</p>
                </div>
            @endforelse
        </div>

        <!-- Extra Decorative Elements -->
        <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full border border-[#d4a574]/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full border border-[#c9b896]/5 blur-3xl pointer-events-none"></div>
    </div>
</div>

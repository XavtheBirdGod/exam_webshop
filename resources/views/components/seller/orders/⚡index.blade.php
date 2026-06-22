<div class="relative min-h-[80dvh] py-12 px-6 max-w-7xl mx-auto w-full" wire:poll.5s>
    <!-- Breadcrumbs & Title -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-white/10 pb-8 mb-12">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-[#9a9590]">
                <a href="/seller/dashboard" class="hover:text-[#d4a574]">Dashboard</a>
                <span>/</span>
                <span class="text-[#e8e4df]">Orders</span>
            </div>
            <h1 class="text-4xl font-bold text-[#e8e4df] mt-2">Manage Orders</h1>
        </div>
    </div>

    <!-- Orders List Card -->
    <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
        @if($orders->isEmpty())
            <div class="text-center py-16 text-[#9a9590]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-[#c9b896] mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h3 class="text-xl font-bold mb-2 text-[#e8e4df]">No Orders Yet</h3>
                <p class="text-sm">When customers place orders, they will appear here in real-time.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 text-xs font-mono text-[#9a9590] uppercase tracking-wider">
                            <th class="py-4">Order ID</th>
                            <th class="py-4">Customer</th>
                            <th class="py-4">Date</th>
                            <th class="py-4">Total</th>
                            <th class="py-4">Status</th>
                            <th class="py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm text-[#e8e4df]">
                        @foreach($orders as $order)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-6 font-mono text-[#c9b896]">
                                    #{{ $order->id }}
                                </td>
                                <td class="py-6">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-[#e8e4df]">{{ $order->shipping_address['first_name'] ?? '' }} {{ $order->shipping_address['last_name'] ?? '' }}</span>
                                        <span class="text-xs text-[#9a9590]">{{ $order->email }}</span>
                                    </div>
                                </td>
                                <td class="py-6 text-[#9a9590]">
                                    {{ $order->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="py-6 font-mono text-[#d4a574] font-semibold">
                                    €{{ number_format($order->total_amount / 100, 2) }}
                                </td>
                                <td class="py-6">
                                    @if($order->status === 'paid')
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Paid
                                        </span>
                                    @elseif($order->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Pending
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#9a9590]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#9a9590]"></span> {{ ucfirst($order->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-6 text-right">
                                    <a href="{{ route('seller.orders.detail', $order) }}" wire:navigate class="px-4 py-2 rounded-full bg-white/5 hover:bg-white/10 text-[#e8e4df] transition-colors text-xs font-semibold uppercase tracking-wider">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
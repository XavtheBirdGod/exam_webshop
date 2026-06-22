<div class="relative min-h-[80dvh] py-12 px-6 max-w-7xl mx-auto w-full" wire:poll.10s>
    <!-- Breadcrumbs & Title -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-white/10 pb-8 mb-12">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-[#9a9590]">
                <a href="/seller/dashboard" class="hover:text-[#d4a574]">Dashboard</a>
                <span>/</span>
                <a href="{{ route('seller.orders.index') }}" wire:navigate class="hover:text-[#d4a574]">Orders</a>
                <span>/</span>
                <span class="text-[#e8e4df]">#{{ $order->id }}</span>
            </div>
            <div class="flex items-center gap-4 mt-2">
                <h1 class="text-4xl font-bold text-[#e8e4df]">Order #{{ $order->id }}</h1>
                @if($order->status === 'paid')
                    <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold tracking-wider uppercase">Paid</span>
                @elseif($order->status === 'pending')
                    <span class="px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold tracking-wider uppercase">Pending</span>
                @else
                    <span class="px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-semibold tracking-wider uppercase">{{ $order->status }}</span>
                @endif
            </div>
            <p class="text-sm text-[#9a9590] mt-2">Placed on {{ $order->created_at->format('F j, Y \a\t H:i') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Line Items -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Line Items</h2>
                <div class="divide-y divide-white/5">
                    @foreach($order->items as $item)
                        <div class="py-4 flex justify-between items-center">
                            <div class="flex gap-4 items-center">
                                @if($item->productVariant && $item->productVariant->product && $item->productVariant->product->images->count() > 0)
                                    <img src="{{ Storage::url($item->productVariant->product->images->first()->path) }}" class="w-16 h-16 rounded-xl object-cover border border-white/10">
                                @else
                                    <div class="w-16 h-16 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#9a9590]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-bold text-[#e8e4df]">{{ $item->name }}</p>
                                    <p class="text-xs text-[#9a9590] mt-1">{{ $item->quantity }} x €{{ number_format($item->price / 100, 2) }}</p>
                                </div>
                            </div>
                            <div class="font-mono font-semibold text-[#d4a574]">
                                €{{ number_format(($item->price * $item->quantity) / 100, 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-white/10 mt-6 pt-6 flex justify-between items-end">
                    <span class="text-[#9a9590] font-semibold">Total Amount</span>
                    <span class="text-3xl font-bold text-[#e8e4df]">
                        €{{ number_format($order->total_amount / 100, 2) }}
                    </span>
                </div>
            </div>

            <!-- Payment Logs -->
            @if($order->paymentLogs->count() > 0)
                <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                    <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Payment History</h2>
                    <div class="space-y-4">
                        @foreach($order->paymentLogs as $log)
                            <div class="p-4 rounded-xl border border-white/5 bg-white/[0.02] flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-semibold text-[#e8e4df] uppercase tracking-wide">{{ $log->provider }} - {{ $log->status }}</p>
                                    <p class="text-xs text-[#9a9590] mt-1 font-mono">TXN: {{ $log->transaction_id ?? 'N/A' }}</p>
                                    <p class="text-xs text-[#9a9590] mt-1">{{ $log->created_at->format('M d, Y H:i:s') }}</p>
                                </div>
                                <div class="font-mono text-[#d4a574] font-semibold text-sm">
                                    €{{ number_format($log->amount / 100, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Customer Details -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h3 class="font-bold text-[#e8e4df] mb-6 text-lg">Customer</h3>
                <p class="text-[#d4a574] font-semibold">{{ $order->shipping_address['first_name'] ?? '' }} {{ $order->shipping_address['last_name'] ?? '' }}</p>
                <a href="mailto:{{ $order->email }}" class="text-[#9a9590] text-sm hover:text-[#e8e4df] transition-colors mt-1 inline-block">{{ $order->email }}</a>
            </div>

            <!-- Shipping Address -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h3 class="font-bold text-[#e8e4df] mb-6 text-lg">Shipping Address</h3>
                <address class="text-[#9a9590] text-sm not-italic space-y-1">
                    <p>{{ $order->shipping_address['address'] ?? 'N/A' }}</p>
                    <p>{{ $order->shipping_address['zip_code'] ?? '' }} {{ $order->shipping_address['city'] ?? '' }}</p>
                </address>
            </div>
            
            <!-- Payment Info -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h3 class="font-bold text-[#e8e4df] mb-6 text-lg">Payment Info</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-[#9a9590] uppercase tracking-wider mb-1">Method</p>
                        <p class="text-sm text-[#e8e4df] font-semibold">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                    </div>
                    @if($order->transaction_id)
                        <div>
                            <p class="text-xs text-[#9a9590] uppercase tracking-wider mb-1">Transaction ID</p>
                            <p class="text-xs font-mono text-[#c9b896] break-all">{{ $order->transaction_id }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
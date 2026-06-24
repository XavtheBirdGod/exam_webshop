<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Tenant;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\User;
use App\Enums\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

new #[Layout('components.layouts.app')] class extends Component
{
    public string $dateRange = 'all'; // all, today, week, month
    public string $selectedVendor = 'all';
    public string $activeTab = 'overview'; // overview, boutiques, users

    public string $newBoutiqueName = '';
    public string $newBoutiqueDomain = '';

    // Create user form fields
    public string $newUserName = '';
    public string $newUserEmail = '';
    public string $newUserPassword = '';
    public string $newUserRole = 'customer';

    // Per-row role editing: userId => selected role value
    public array $editingRoles = [];

    #[Computed]
    public function dashboardData()
    {
        $tenants = Tenant::with('domains')->get();
        
        $totalRevenue = 0;
        $totalOrders = 0;
        $revenuePerVendor = collect();
        $failedPayments = collect();
        
        $startDate = $this->getStartDate();

        foreach ($tenants as $tenant) {
            if ($this->selectedVendor !== 'all' && $tenant->id !== $this->selectedVendor) {
                continue;
            }

            try {
                $tenant->run(function () use ($tenant, &$totalRevenue, &$totalOrders, &$revenuePerVendor, &$failedPayments, $startDate) {
                    $ordersQuery = Order::where('status', 'paid');
                    $failedQuery = PaymentLog::where('status', '!=', 'succeeded')->with('order');

                    if ($startDate) {
                        $ordersQuery->where('created_at', '>=', $startDate);
                        $failedQuery->where('created_at', '>=', $startDate);
                    }

                    $revenue = $ordersQuery->sum('total_amount');
                    $count = $ordersQuery->count();
                    
                    $totalRevenue += $revenue;
                    $totalOrders += $count;
                    
                    $revenuePerVendor->push([
                        'id' => $tenant->id,
                        'name' => ucfirst(str_replace(['shop-', 'rituals-'], '', $tenant->id)),
                        'domain' => $tenant->domains->first()?->domain ?? 'N/A',
                        'revenue' => $revenue,
                        'orders_count' => $count,
                    ]);

                    $failed = $failedQuery->get()->map(function ($log) use ($tenant) {
                        return [
                            'tenant_name' => ucfirst(str_replace(['shop-', 'rituals-'], '', $tenant->id)),
                            'provider' => $log->provider,
                            'status' => $log->status,
                            'amount' => $log->amount,
                            'date' => $log->created_at,
                            'order_id' => $log->order_id,
                        ];
                    });

                    $failedPayments = $failedPayments->concat($failed);
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Failed to fetch dashboard data for tenant {$tenant->id}: " . $e->getMessage());
                
                // Add placeholder info to prevent breaking the overview lists
                $revenuePerVendor->push([
                    'id' => $tenant->id,
                    'name' => ucfirst(str_replace(['shop-', 'rituals-'], '', $tenant->id)) . ' (Error)',
                    'domain' => $tenant->domains->first()?->domain ?? 'N/A',
                    'revenue' => 0,
                    'orders_count' => 0,
                ]);
            }
        }

        return [
            'active_tenants' => $this->selectedVendor === 'all' ? $tenants->count() : 1,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'revenue_per_vendor' => $revenuePerVendor->sortByDesc('revenue')->values(),
            'failed_payments' => $failedPayments->sortByDesc('date')->take(10)->values(),
        ];
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('created_at', 'desc')->get();
    }

    public function mount()
    {
        // Pre-populate editingRoles so each row has its current role pre-selected
        foreach (User::all() as $user) {
            $this->editingRoles[$user->id] = $user->role->value;
        }
    }

    public function createUser()
    {
        $this->validate([
            'newUserName'     => 'required|string|min:2|max:255',
            'newUserEmail'    => 'required|email|unique:users,email',
            'newUserPassword' => 'required|string|min:8',
            'newUserRole'     => ['required', Rule::in(array_column(Role::cases(), 'value'))],
        ]);

        $user = User::create([
            'name'     => $this->newUserName,
            'email'    => $this->newUserEmail,
            'password' => Hash::make($this->newUserPassword),
            'role'     => Role::from($this->newUserRole),
        ]);

        $this->editingRoles[$user->id] = $user->role->value;

        $this->newUserName     = '';
        $this->newUserEmail    = '';
        $this->newUserPassword = '';
        $this->newUserRole     = 'customer';

        session()->flash('user_status', 'User created successfully!');
    }

    public function updateUserRole(int $userId)
    {
        $this->validate([
            "editingRoles.{$userId}" => ['required', Rule::in(array_column(Role::cases(), 'value'))],
        ]);

        $user = User::findOrFail($userId);

        // Prevent the admin from demoting themselves
        if ($user->id === auth()->id()) {
            session()->flash('user_error', 'You cannot change your own role.');
            return;
        }

        $user->update(['role' => Role::from($this->editingRoles[$userId])]);

        session()->flash('user_status', "Role updated for {$user->name}.");
    }

    public function createBoutique()
    {
        $this->validate([
            'newBoutiqueName' => 'required|string|min:2|max:255',
            'newBoutiqueDomain' => 'required|string|min:3|max:255|unique:domains,domain',
        ]);

        $slug = \Illuminate\Support\Str::slug($this->newBoutiqueName);
        $tenantId = 'shop-' . $slug;

        // Check if tenant ID already exists
        if (Tenant::where('id', $tenantId)->exists()) {
            $tenantId = $tenantId . '-' . time();
        }

        // Create Tenant
        $tenant = Tenant::create(['id' => $tenantId]);

        // Create Domain
        $tenant->domains()->create(['domain' => $this->newBoutiqueDomain]);

        // Seed default shop data and products/categories
        $tenant->run(function () use ($tenant) {
            \App\Models\Shop::create([
                'name' => $this->newBoutiqueName,
                'slug' => \Illuminate\Support\Str::slug($this->newBoutiqueName),
                'email' => 'info@' . $this->newBoutiqueDomain,
            ]);

            $seeder = new \Database\Seeders\ProductSeeder();
            $seeder->run();
        });

        $this->newBoutiqueName = '';
        $this->newBoutiqueDomain = '';

        session()->flash('status', 'Boutique created successfully!');
    }

    public function deleteBoutique(string $tenantId)
    {
        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            $tenant->delete();
            session()->flash('status', 'Boutique deleted successfully!');
        }
    }

    private function getStartDate()
    {
        return match($this->dateRange) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subDays(7),
            'month' => Carbon::now()->subDays(30),
            default => null,
        };
    }
};
?>

<div class="relative min-h-[80dvh] py-12 px-6 max-w-7xl mx-auto w-full">
    <!-- Breadcrumbs & Title -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-white/10 pb-8 mb-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-[#9a9590]">
                <span class="text-[#e8e4df]">Admin</span>
                <span>/</span>
                <span class="text-[#d4a574]">Platform Dashboard</span>
            </div>
            <h1 class="text-4xl font-bold text-[#e8e4df] mt-2">Platform Overview</h1>
            <p class="text-[#9a9590] mt-2 text-sm max-w-xl">
                Real-time insights across all multi-tenant boutiques. Manage revenue, track orders, and monitor system health.
            </p>
        </div>
        
        <div class="mt-6 md:mt-0 flex gap-4">
            <!-- Vendor Filter -->
            <select wire:model.live="selectedVendor" class="bg-[#161615] border border-white/10 text-[#e8e4df] text-sm rounded-xl px-4 py-2.5 focus:ring-[#d4a574] focus:border-[#d4a574] outline-none transition-colors cursor-pointer appearance-none pr-10 relative">
                <option value="all">All Boutiques</option>
                @foreach(App\Models\Tenant::all() as $t)
                    <option value="{{ $t->id }}">Rituals {{ ucfirst(str_replace(['shop-', 'rituals-'], '', $t->id)) }}</option>
                @endforeach
            </select>

            <!-- Date Filter -->
            <select wire:model.live="dateRange" class="bg-[#161615] border border-white/10 text-[#e8e4df] text-sm rounded-xl px-4 py-2.5 focus:ring-[#d4a574] focus:border-[#d4a574] outline-none transition-colors cursor-pointer appearance-none pr-10 relative">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">Last 7 Days</option>
                <option value="month">Last 30 Days</option>
            </select>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex gap-6 border-b border-white/10 mb-10">
        <button type="button" wire:click="$set('activeTab', 'overview')" class="pb-4 px-2 font-accent text-sm uppercase tracking-wider transition-all relative {{ $this->activeTab === 'overview' ? 'text-[#d4a574] font-semibold' : 'text-[#9a9590] hover:text-[#e8e4df]' }}">
            Overview
            @if($this->activeTab === 'overview')
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#d4a574]"></div>
            @endif
        </button>
        <button type="button" wire:click="$set('activeTab', 'boutiques')" class="pb-4 px-2 font-accent text-sm uppercase tracking-wider transition-all relative {{ $this->activeTab === 'boutiques' ? 'text-[#d4a574] font-semibold' : 'text-[#9a9590] hover:text-[#e8e4df]' }}">
            Manage Boutiques
            @if($this->activeTab === 'boutiques')
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#d4a574]"></div>
            @endif
        </button>
        <button type="button" wire:click="$set('activeTab', 'users')" class="pb-4 px-2 font-accent text-sm uppercase tracking-wider transition-all relative {{ $this->activeTab === 'users' ? 'text-[#d4a574] font-semibold' : 'text-[#9a9590] hover:text-[#e8e4df]' }}">
            Platform Users
            @if($this->activeTab === 'users')
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#d4a574]"></div>
            @endif
        </button>
    </div>

    <!-- Loading Overlay -->
    <div wire:loading class="fixed inset-0 z-50 bg-[#0f0f0f]/50 backdrop-blur-sm flex items-center justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#d4a574]"></div>
    </div>

    @if($this->activeTab === 'overview')
        <!-- Top Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Total Revenue -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] relative overflow-hidden group hover:border-[#d4a574]/30 transition-colors">
                <div class="absolute top-0 right-0 p-8 text-[#d4a574]/10 group-hover:text-[#d4a574]/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 absolute -top-4 -right-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-mono uppercase tracking-widest text-[#9a9590] mb-2 relative z-10">Total Revenue</h3>
                <p class="text-4xl font-bold text-[#e8e4df] font-mono relative z-10">
                    €{{ number_format($this->dashboardData['total_revenue'] / 100, 2) }}
                </p>
            </div>

            <!-- Total Orders -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] relative overflow-hidden group hover:border-[#e8b4b8]/30 transition-colors">
                <div class="absolute top-0 right-0 p-8 text-[#e8b4b8]/10 group-hover:text-[#e8b4b8]/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 absolute -top-4 -right-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h3 class="text-sm font-mono uppercase tracking-widest text-[#9a9590] mb-2 relative z-10">Total Orders</h3>
                <p class="text-4xl font-bold text-[#e8e4df] font-mono relative z-10">
                    {{ number_format($this->dashboardData['total_orders']) }}
                </p>
            </div>

            <!-- Active Locations -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] relative overflow-hidden group hover:border-[#c9b896]/30 transition-colors">
                <div class="absolute top-0 right-0 p-8 text-[#c9b896]/10 group-hover:text-[#c9b896]/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 absolute -top-4 -right-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-sm font-mono uppercase tracking-widest text-[#9a9590] mb-2 relative z-10">Active Boutiques</h3>
                <p class="text-4xl font-bold text-[#e8e4df] font-mono relative z-10">
                    {{ $this->dashboardData['active_tenants'] }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Revenue Breakdown -->
            <div class="xl:col-span-2 bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Revenue Breakdown</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/10 text-xs font-mono text-[#9a9590] uppercase tracking-wider">
                                <th class="py-4">Boutique</th>
                                <th class="py-4">Domain</th>
                                <th class="py-4 text-right">Orders</th>
                                <th class="py-4 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm text-[#e8e4df]">
                            @foreach($this->dashboardData['revenue_per_vendor'] as $vendor)
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="py-5 font-bold text-[#e8e4df]">{{ $vendor['name'] }}</td>
                                    <td class="py-5 text-[#9a9590]">{{ $vendor['domain'] }}</td>
                                    <td class="py-5 text-right font-mono">{{ number_format($vendor['orders_count']) }}</td>
                                    <td class="py-5 text-right font-mono font-semibold text-[#d4a574]">
                                        €{{ number_format($vendor['revenue'] / 100, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($this->dashboardData['revenue_per_vendor']) === 0)
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-[#9a9590]">No data available.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Failed Payments Overview -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-[#e8e4df]">Failed Transactions</h2>
                    <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 text-xs font-semibold">{{ count($this->dashboardData['failed_payments']) }} logs</span>
                </div>
                
                <div class="space-y-4 flex-grow overflow-y-auto max-h-[400px] pr-2 custom-scrollbar">
                    @forelse($this->dashboardData['failed_payments'] as $log)
                        <div class="p-4 rounded-xl border border-white/5 bg-white/[0.02] hover:bg-white/[0.04] transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="text-xs font-mono uppercase text-[#e8b4b8] tracking-widest">{{ $log['tenant_name'] }}</span>
                                    <p class="text-sm font-semibold text-[#e8e4df] mt-1">Order #{{ $log['order_id'] }}</p>
                                </div>
                                <span class="font-mono text-sm text-[#9a9590]">€{{ number_format($log['amount'] / 100, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-end mt-3">
                                <span class="text-xs text-[#9a9590] uppercase tracking-wider">{{ $log['provider'] }} &bull; {{ $log['status'] }}</span>
                                <span class="text-xs text-[#555]">{{ \Carbon\Carbon::parse($log['date'])->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center py-12">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#9a9590]/50 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-[#9a9590] text-sm">No failed transactions.</p>
                            <p class="text-[#9a9590]/50 text-xs mt-1">System health is optimal.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @elseif($this->activeTab === 'boutiques')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add Boutique Form -->
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] h-fit">
                <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Add New Boutique</h2>
                
                <form wire:submit.prevent="createBoutique" class="space-y-6">
                    @if (session()->has('status'))
                        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-[#9a9590] mb-2">Boutique Name</label>
                        <input type="text" wire:model="newBoutiqueName" placeholder="e.g. Brussels" class="w-full px-4 py-3 bg-[#0f0f0f] rounded-xl border border-white/10 text-[#e8e4df] placeholder-[#555] focus:outline-none focus:border-[#d4a574] transition-all text-sm" />
                        @error('newBoutiqueName') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-[#9a9590] mb-2">Domain Name</label>
                        <input type="text" wire:model="newBoutiqueDomain" placeholder="e.g. brussels.localhost" class="w-full px-4 py-3 bg-[#0f0f0f] rounded-xl border border-white/10 text-[#e8e4df] placeholder-[#555] focus:outline-none focus:border-[#d4a574] transition-all text-sm" />
                        @error('newBoutiqueDomain') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-[#d4a574] text-[#0f0f0f] font-bold text-sm hover:brightness-110 active:scale-[0.98] transition-all">
                        Create Boutique
                    </button>
                </form>
            </div>

            <!-- Boutiques List -->
            <div class="lg:col-span-2 bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h2 class="text-xl font-bold text-[#e8e4df] mb-6 font-accent">Active Locations</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/10 text-xs font-mono text-[#9a9590] uppercase tracking-wider">
                                <th class="py-4">Boutique</th>
                                <th class="py-4">Domain</th>
                                <th class="py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm text-[#e8e4df]">
                            @foreach(App\Models\Tenant::with('domains')->get() as $tenant)
                                @php
                                    $domain = $tenant->domains->first()?->domain ?? 'N/A';
                                    $name = ucfirst(str_replace(['shop-', 'rituals-'], '', $tenant->id));
                                @endphp
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="py-5 font-bold text-[#e8e4df]">{{ $name }}</td>
                                    <td class="py-5 text-[#9a9590]">{{ $domain }}</td>
                                    <td class="py-5 text-right">
                                        <button type="button" 
                                            wire:click="deleteBoutique('{{ $tenant->id }}')" 
                                            wire:confirm="Are you sure you want to delete the boutique Rituals {{ $name }}? This will permanently delete its database and all associated records."
                                            class="px-4 py-2 rounded-xl border border-red-500/20 text-red-400 hover:bg-red-500/10 text-xs font-semibold transition-all"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($this->activeTab === 'users')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Create New User Form --}}
            <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] h-fit">
                <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Create New User</h2>

                <form wire:submit.prevent="createUser" class="space-y-5">
                    @if (session()->has('user_status'))
                        <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
                            {{ session('user_status') }}
                        </div>
                    @endif
                    @if (session()->has('user_error'))
                        <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                            {{ session('user_error') }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-[#9a9590] mb-2">Full Name</label>
                        <input id="new-user-name" type="text" wire:model="newUserName" placeholder="Jane Doe"
                            class="w-full px-4 py-3 bg-[#0f0f0f] rounded-xl border border-white/10 text-[#e8e4df] placeholder-[#555] focus:outline-none focus:border-[#d4a574] transition-all text-sm" />
                        @error('newUserName') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-[#9a9590] mb-2">Email Address</label>
                        <input id="new-user-email" type="email" wire:model="newUserEmail" placeholder="jane@example.com"
                            class="w-full px-4 py-3 bg-[#0f0f0f] rounded-xl border border-white/10 text-[#e8e4df] placeholder-[#555] focus:outline-none focus:border-[#d4a574] transition-all text-sm" />
                        @error('newUserEmail') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-[#9a9590] mb-2">Password</label>
                        <input id="new-user-password" type="password" wire:model="newUserPassword" placeholder="Min. 8 characters"
                            class="w-full px-4 py-3 bg-[#0f0f0f] rounded-xl border border-white/10 text-[#e8e4df] placeholder-[#555] focus:outline-none focus:border-[#d4a574] transition-all text-sm" />
                        @error('newUserPassword') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-[#9a9590] mb-2">Role</label>
                        <select id="new-user-role" wire:model="newUserRole"
                            class="w-full px-4 py-3 bg-[#0f0f0f] rounded-xl border border-white/10 text-[#e8e4df] focus:outline-none focus:border-[#d4a574] transition-all text-sm appearance-none cursor-pointer">
                            @foreach(App\Enums\Role::cases() as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @error('newUserRole') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" id="create-user-submit"
                        class="w-full py-3 rounded-xl bg-[#d4a574] text-[#0f0f0f] font-bold text-sm hover:brightness-110 active:scale-[0.98] transition-all">
                        Create User
                    </button>
                </form>
            </div>

            {{-- Users Table --}}
            <div class="lg:col-span-2 bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
                <h2 class="text-xl font-bold text-[#e8e4df] mb-6 font-accent">Platform Users</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/10 text-xs font-mono text-[#9a9590] uppercase tracking-wider">
                                <th class="py-4">Name</th>
                                <th class="py-4">Email</th>
                                <th class="py-4">Role</th>
                                <th class="py-4">Joined</th>
                                <th class="py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm text-[#e8e4df]">
                            @foreach($this->users as $user)
                                <tr class="hover:bg-white/[0.02] transition-colors" wire:key="user-{{ $user->id }}">
                                    <td class="py-4 font-bold text-[#e8e4df]">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="ml-1 text-[10px] font-mono text-[#d4a574] uppercase tracking-wider">(you)</span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-[#9a9590] text-xs">{{ $user->email }}</td>
                                    <td class="py-4">
                                        @if($user->id === auth()->id())
                                            {{-- Cannot change own role --}}
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#d4a574]/10 text-[#d4a574] border border-[#d4a574]/20">
                                                {{ $user->role->label() }}
                                            </span>
                                        @else
                                            <select wire:model="editingRoles.{{ $user->id }}"
                                                class="bg-[#0f0f0f] border border-white/10 text-[#e8e4df] text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:border-[#d4a574] transition-colors cursor-pointer appearance-none">
                                                @foreach(App\Enums\Role::cases() as $role)
                                                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                    <td class="py-4 text-[#9a9590] font-mono text-xs">{{ $user->created_at->format('Y-m-d') }}</td>
                                    <td class="py-4 text-right">
                                        @if($user->id !== auth()->id())
                                            <button type="button"
                                                wire:click="updateUserRole({{ $user->id }})"
                                                wire:confirm="Update role for {{ $user->name }}?"
                                                class="px-3 py-1.5 rounded-lg border border-[#d4a574]/30 text-[#d4a574] hover:bg-[#d4a574]/10 text-xs font-semibold transition-all">
                                                Save
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

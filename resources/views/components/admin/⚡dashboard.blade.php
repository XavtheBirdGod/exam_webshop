<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use App\Livewire\Forms\ProductForm;
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
    use WithFileUploads;

    public string $dateRange = 'all'; // all, today, week, month
    public string $selectedVendor = 'all';
    public string $activeTab = 'overview'; // overview, boutiques, users, products, orders

    public string $newBoutiqueName = '';
    public string $newBoutiqueDomain = '';

    public string $productSearch = '';
    public string $orderSearch = '';
    public string $orderStatusFilter = 'all';

    // Order detail state
    public ?array $viewingOrder = null;

    // Product Editing State
    public ?int $editingProductId = null;
    public ?string $editingTenantId = null;
    public ProductForm $form;

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

    #[Computed]
    public function allOrders()
    {
        $tenants = Tenant::with('domains')->get();
        $orders  = collect();

        foreach ($tenants as $tenant) {
            if ($this->selectedVendor !== 'all' && $tenant->id !== $this->selectedVendor) {
                continue;
            }
            try {
                $tenant->run(function () use ($tenant, &$orders) {
                    $query = \App\Models\Order::with('items')->latest();

                    if ($this->orderStatusFilter !== 'all') {
                        $query->where('status', $this->orderStatusFilter);
                    }
                    if ($this->orderSearch) {
                        $search = $this->orderSearch;
                        $query->where(function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%")
                              ->orWhere('id', 'like', "%{$search}%")
                              ->orWhere('transaction_id', 'like', "%{$search}%");
                        });
                    }

                    $tenantOrders = $query->get()->map(function ($order) use ($tenant) {
                        return (object) [
                            'id'              => $order->id,
                            'tenant_id'       => $tenant->id,
                            'tenant_name'     => ucfirst(str_replace(['shop-', 'rituals-'], '', $tenant->id)),
                            'email'           => $order->email,
                            'status'          => $order->status,
                            'total_amount'    => $order->total_amount,
                            'payment_method'  => $order->payment_method,
                            'transaction_id'  => $order->transaction_id,
                            'items_count'     => $order->items->count(),
                            'items'           => $order->items->map(fn($i) => [
                                'name'     => $i->name,
                                'price'    => $i->price,
                                'quantity' => $i->quantity,
                            ])->toArray(),
                            'shipping_address' => $order->shipping_address,
                            'billing_address'  => $order->billing_address,
                            'created_at'      => $order->created_at->toDateTimeString(),
                        ];
                    });

                    $orders = $orders->concat($tenantOrders);
                });
            } catch (\Throwable $e) {
                // Ignore failed tenants
            }
        }

        return $orders->sortByDesc('created_at')->values();
    }

    public function viewOrder(string $tenantId, int $orderId)
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenant->run(function () use ($orderId, $tenantId) {
            $order = \App\Models\Order::with('items')->find($orderId);
            if ($order) {
                $this->viewingOrder = [
                    'id'               => $order->id,
                    'tenant_id'        => $tenantId,
                    'tenant_name'      => ucfirst(str_replace(['shop-', 'rituals-'], '', $tenantId)),
                    'email'            => $order->email,
                    'status'           => $order->status,
                    'total_amount'     => $order->total_amount,
                    'payment_method'   => $order->payment_method,
                    'transaction_id'   => $order->transaction_id,
                    'items'            => $order->items->map(fn($i) => [
                        'name'     => $i->name,
                        'price'    => $i->price,
                        'quantity' => $i->quantity,
                    ])->toArray(),
                    'shipping_address' => $order->shipping_address,
                    'billing_address'  => $order->billing_address,
                    'created_at'       => $order->created_at->format('d M Y, H:i'),
                    'updated_at'       => $order->updated_at->format('d M Y, H:i'),
                ];
            }
        });
    }

    public function closeOrder()
    {
        $this->viewingOrder = null;
    }

    #[Computed]
    public function allProducts()
    {
        $tenants = Tenant::with('domains')->get();
        $products = collect();

        foreach ($tenants as $tenant) {
            try {
                $tenantBaseUrl = $this->tenantAssetBaseUrl($tenant);
                $tenant->run(function () use ($tenant, &$products, $tenantBaseUrl) {
                    $query = \App\Models\Product::with(['category', 'images', 'primaryImage']);
                    if ($this->productSearch) {
                        $query->where('name', 'like', '%' . $this->productSearch . '%');
                    }
                    
                    $tenantProducts = $query->get()->map(function ($product) use ($tenant, $tenantBaseUrl) {
                        // Build image URL directly from tenant domain to avoid tenant_asset() failing on central domain
                        $imageUrl = null;
                        $primary = $product->primaryImage;
                        if ($primary) {
                            $imageUrl = $tenantBaseUrl . '/tenancy/assets/' . ltrim($primary->path, '/');
                        } else {
                            $first = $product->images->first();
                            if ($first) {
                                $imageUrl = $tenantBaseUrl . '/tenancy/assets/' . ltrim($first->path, '/');
                            }
                        }
                        return (object) [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'category_name' => $product->category?->name ?? 'None',
                            'image_url' => $imageUrl ?? 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400"><rect width="100%" height="100%" fill="%23161615"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%23d4a574" font-family="sans-serif">No Image</text></svg>',
                            'tenant_id' => $tenant->id,
                            'tenant_name' => ucfirst(str_replace(['shop-', 'rituals-'], '', $tenant->id)),
                            'tenant_domain' => $tenant->domains->first()?->domain,
                        ];
                    });
                    
                    $products = $products->concat($tenantProducts);
                });
            } catch (\Throwable $e) {
                // Ignore failed tenants
            }
        }

        return $products;
    }

    public function deleteProduct(string $tenantId, int $productId)
    {
        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            $tenant->run(function () use ($productId) {
                $product = \App\Models\Product::find($productId);
                if ($product) {
                    $product->delete();
                }
            });
            session()->flash('product_status', 'Product deleted successfully!');
        }
    }

    #[Computed]
    public function editingCategories()
    {
        if (!$this->editingTenantId) return [];

        $tenant = Tenant::find($this->editingTenantId);
        $categories = [];
        if ($tenant) {
            $tenant->run(function () use (&$categories) {
                // Return simple objects/arrays instead of Models to avoid connection mismatch
                $categories = \App\Models\Category::all()->map(function($c) {
                    return (object) ['id' => $c->id, 'name' => $c->name];
                })->toArray();
            });
        }
        return $categories;
    }

    public function editProduct(string $tenantId, int $productId)
    {
        $this->editingTenantId = $tenantId;
        $this->editingProductId = $productId;

        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            $tenantBaseUrl = $this->tenantAssetBaseUrl($tenant);
            $tenant->run(function () use ($productId, $tenantBaseUrl) {
                $product = \App\Models\Product::with(['variants', 'images'])->find($productId);
                if ($product) {
                    $this->form->fillFromProduct($product, $tenantBaseUrl);
                }
            });
        }
    }

    public function cancelEdit()
    {
        $this->editingTenantId = null;
        $this->editingProductId = null;
        $this->form->reset();
    }

    public function saveProduct(\App\Actions\SaveProductAction $action)
    {
        $tenantId = $this->editingTenantId;
        $productId = $this->editingProductId;

        if (!$tenantId || !$productId) return;

        // Collect absolute paths of uploaded temporary images before tenant switch
        $realPaths = [];
        foreach ($this->form->images as $image) {
            if ($image) {
                $realPaths[] = $image->getRealPath();
            }
        }

        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            $tenant->run(function () use ($productId, $action, $realPaths) {
                $uploadedPaths = [];
                // Store the images manually in the tenant's public disk
                foreach ($realPaths as $path) {
                    $newPath = \Illuminate\Support\Facades\Storage::disk('public')
                        ->putFile('products', new \Illuminate\Http\File($path));
                    $uploadedPaths[] = $newPath;
                }

                // Clear the form's temporary images so ProductForm doesn't try to store them
                $this->form->images = [];

                $product = \App\Models\Product::with(['variants', 'images'])->find($productId);
                if ($product) {
                    $this->form->store($action, $product, $uploadedPaths);
                }
            });

            session()->flash('product_status', 'Product successfully updated directly on ' . $tenant->id);
        }

        $this->cancelEdit();
    }

    public function addVariant()
    {
        $this->form->variants[] = [
            'id' => null,
            'name' => 'Size',
            'value' => '',
            'sku' => '',
            'price_modifier' => 0,
            'stock_on_hand' => 0,
        ];
    }

    public function removeVariant($index)
    {
        unset($this->form->variants[$index]);
        $this->form->variants = array_values($this->form->variants);
    }

    public function removeNewImage($index)
    {
        $images = $this->form->images;
        unset($images[$index]);
        $this->form->images = array_values($images);
    }

    public function markExistingImageForDeletion($imageId)
    {
        $this->form->deleteImageIds[] = $imageId;
        $this->form->existingImages = array_values(
            array_filter($this->form->existingImages, fn($img) => $img['id'] !== $imageId)
        );
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

    private function tenantAssetBaseUrl(Tenant $tenant): string
    {
        // Build a base URL using the tenant's own domain, mirroring the APP_URL scheme+port
        // e.g. APP_URL=http://localhost:8000 + tenant domain 'amsterdam.localhost'
        // => 'http://amsterdam.localhost:8000'
        $appUrl   = rtrim(config('app.url'), '/');
        $parsed   = parse_url($appUrl);
        $scheme   = $parsed['scheme'] ?? 'http';
        $port     = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $domain   = $tenant->domains->first()?->domain ?? 'localhost';
        return $scheme . '://' . $domain . $port;
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
    <div class="flex gap-6 border-b border-white/10 mb-10 overflow-x-auto">
        <button type="button" wire:click="$set('activeTab', 'overview')" class="pb-4 px-2 font-accent text-sm uppercase tracking-wider transition-all relative whitespace-nowrap {{ $this->activeTab === 'overview' ? 'text-[#d4a574] font-semibold' : 'text-[#9a9590] hover:text-[#e8e4df]' }}">
            Overview
            @if($this->activeTab === 'overview')
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#d4a574]"></div>
            @endif
        </button>
        <button type="button" wire:click="$set('activeTab', 'boutiques')" class="pb-4 px-2 font-accent text-sm uppercase tracking-wider transition-all relative whitespace-nowrap {{ $this->activeTab === 'boutiques' ? 'text-[#d4a574] font-semibold' : 'text-[#9a9590] hover:text-[#e8e4df]' }}">
            Manage Boutiques
            @if($this->activeTab === 'boutiques')
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#d4a574]"></div>
            @endif
        </button>
        <button type="button" wire:click="$set('activeTab', 'users')" class="pb-4 px-2 font-accent text-sm uppercase tracking-wider transition-all relative whitespace-nowrap {{ $this->activeTab === 'users' ? 'text-[#d4a574] font-semibold' : 'text-[#9a9590] hover:text-[#e8e4df]' }}">
            Platform Users
            @if($this->activeTab === 'users')
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#d4a574]"></div>
            @endif
        </button>
        <button type="button" wire:click="$set('activeTab', 'products')" class="pb-4 px-2 font-accent text-sm uppercase tracking-wider transition-all relative whitespace-nowrap {{ $this->activeTab === 'products' ? 'text-[#d4a574] font-semibold' : 'text-[#9a9590] hover:text-[#e8e4df]' }}">
            Manage Products
            @if($this->activeTab === 'products')
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-[#d4a574]"></div>
            @endif
        </button>
        <button type="button" wire:click="$set('activeTab', 'orders')" class="pb-4 px-2 font-accent text-sm uppercase tracking-wider transition-all relative whitespace-nowrap {{ $this->activeTab === 'orders' ? 'text-[#d4a574] font-semibold' : 'text-[#9a9590] hover:text-[#e8e4df]' }}">
            Order History
            @if($this->activeTab === 'orders')
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
    @elseif($this->activeTab === 'products')
        @if($this->editingProductId)
            <div class="border-b border-white/10 pb-8 mb-8">
                <h1 class="text-3xl font-bold text-[#e8e4df]">Edit Product</h1>
            </div>
            <form wire:submit.prevent="saveProduct" class="space-y-8">
        <!-- Main Details Section -->
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] space-y-6">
            <h3 class="text-xl font-bold text-[#d4a574] mb-4">Product Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Product Name</label>
                    <input wire:model="form.name" id="name" type="text" required class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                    @error('form.name') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Slug</label>
                    <input wire:model="form.slug" id="slug" type="text" required class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                    @error('form.slug') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Description</label>
                <textarea wire:model="form.description" id="description" rows="4" class="appearance-none rounded-3xl block w-full px-6 py-4 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm"></textarea>
                @error('form.description') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Base Price -->
                <div>
                    <label for="price" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Base Price (€)</label>
                    <input wire:model="form.price" id="price" type="number" step="0.01" required class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                    @error('form.price') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Category</label>
                    <select wire:model="form.category_id" id="category" class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                        <option value="">Select Category</option>
                        @foreach($this->editingCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('form.category_id') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-accent uppercase tracking-widest text-[#9a9590] mb-2">Status</label>
                    <select wire:model="form.status" id="status" class="appearance-none rounded-full block w-full px-6 py-3 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-2 focus:ring-[#d4a574] focus:border-transparent transition-all sm:text-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="draft">Draft</option>
                    </select>
                    @error('form.status') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Featured -->
            <div class="flex items-center">
                <input wire:model="form.featured" id="featured" type="checkbox" value="1" class="h-4 w-4 text-[#d4a574] focus:ring-[#d4a574] border-white/10 rounded bg-[#0f0f0f]">
                <label for="featured" class="ml-2 block text-sm text-[#9a9590] font-accent uppercase tracking-widest">Featured Product</label>
            </div>
        </div>

        <!-- Product Images Section -->
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] space-y-6">
            <h3 class="text-xl font-bold text-[#d4a574] mb-4">Product Images</h3>

            <!-- Existing saved images -->
            @if(!empty($form->existingImages))
                <div>
                    <p class="text-xs font-mono uppercase tracking-wider text-[#9a9590] mb-3">Saved Images</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach($form->existingImages as $img)
                            <div class="relative group rounded-2xl overflow-hidden border border-white/10 aspect-square">
                                <img src="{{ $img['url'] }}" alt="Product image" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button"
                                        wire:click="markExistingImageForDeletion({{ $img['id'] }})"
                                        wire:confirm="Remove this image from the product?"
                                        class="p-2 rounded-full bg-red-500/80 hover:bg-red-500 text-white transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                @if($img['is_primary'])
                                    <span class="absolute top-2 left-2 text-[10px] font-mono uppercase tracking-wider bg-[#d4a574] text-[#0f0f0f] px-2 py-0.5 rounded-full">Primary</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Upload area for new images -->
            <div>
                <p class="text-xs font-mono uppercase tracking-wider text-[#9a9590] mb-3">Add More Images</p>
                <div x-data="{ dragging: false }"
                     @dragover.prevent="dragging = true"
                     @dragleave.prevent="dragging = false"
                     @drop.prevent="dragging = false"
                     :class="dragging ? 'border-[#d4a574] bg-[#d4a574]/5' : 'border-white/10 hover:border-white/25'"
                     class="relative border-2 border-dashed rounded-2xl p-8 text-center transition-all cursor-pointer">
                    <input type="file"
                           wire:model="form.images"
                           id="product-images-edit"
                           multiple
                           accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto text-[#9a9590] mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-[#9a9590] text-sm font-accent">Drop images here or <span class="text-[#d4a574] underline">click to browse</span></p>
                    <p class="text-[#555] text-xs mt-1 font-mono">JPEG, PNG, WEBP or GIF — max 2 MB each</p>
                </div>
            </div>

            @error('form.images.*') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror

            <!-- Previews of newly staged uploads -->
            @if(!empty($form->images))
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($form->images as $index => $image)
                        <div class="relative group rounded-2xl overflow-hidden border border-white/10 aspect-square">
                            <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button type="button" wire:click="removeNewImage({{ $index }})"
                                    class="p-2 rounded-full bg-red-500/80 hover:bg-red-500 text-white transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <span class="absolute top-2 left-2 text-[10px] font-mono uppercase tracking-wider bg-white/20 text-white px-2 py-0.5 rounded-full">New</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div wire:loading wire:target="form.images" class="text-sm text-[#9a9590] font-accent mt-2">
                Uploading...
            </div>
        </div>

        <!-- Variants Section -->
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)] space-y-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-[#d4a574]">Product Variants & Stock</h3>
                <button type="button" wire:click="addVariant" class="px-5 py-2 rounded-full border border-[#c9b896]/30 hover:bg-white/5 font-semibold text-xs text-[#c9b896] uppercase tracking-wider transition-all">
                    + Add Variant
                </button>
            </div>

            @if(empty($form->variants))
                <p class="text-[#9a9590] text-sm text-center py-6">Please add at least one variant.</p>
            @else
                <div class="space-y-4">
                    @foreach($form->variants as $index => $variant)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-[#0f0f0f]/35 p-6 rounded-2xl border border-white/5 relative">
                            <!-- Type (e.g. Size) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Type</label>
                                <input wire:model="form.variants.{{ $index }}.name" type="text" placeholder="e.g. Size" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Value (e.g. 200 ml) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Value</label>
                                <input wire:model="form.variants.{{ $index }}.value" type="text" placeholder="e.g. 200 ml" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- SKU -->
                            <div class="md:col-span-3">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">SKU</label>
                                <input wire:model="form.variants.{{ $index }}.sku" type="text" placeholder="e.g. RIT-AM-001" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Price Modifier -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Price Mod. (€)</label>
                                <input wire:model="form.variants.{{ $index }}.price_modifier" type="number" step="0.01" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Stock On Hand -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-2">Stock Level</label>
                                <input wire:model="form.variants.{{ $index }}.stock_on_hand" type="number" required class="appearance-none rounded-full block w-full px-4 py-2.5 border border-white/10 bg-[#0f0f0f] text-[#e8e4df] focus:outline-none focus:ring-1 focus:ring-[#d4a574] sm:text-xs">
                            </div>

                            <!-- Remove Button -->
                            <div class="md:col-span-1 flex justify-center">
                                <button type="button" wire:click="removeVariant({{ $index }})" class="p-2.5 rounded-full hover:bg-red-500/10 text-[#9a9590] hover:text-red-400 transition-colors" title="Remove Variant">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @error('form.variants') <span class="text-xs text-red-400 mt-1 block font-accent">{{ $message }}</span> @enderror
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-4 justify-end">
            <button type="button" wire:click="cancelEdit" class="px-8 py-4 rounded-full border border-white/10 hover:bg-white/5 font-bold text-sm text-[#e8e4df] transition-all">
                Cancel
            </button>
            <button type="submit" class="px-10 py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-sm hover:brightness-110 active:scale-95 transition-all uppercase tracking-widest">
                Save Changes
            </button>
        </div>
    </form>
        @else
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <h2 class="text-xl font-bold text-[#e8e4df] font-accent">Cross-Store Catalog</h2>
                
                <div class="relative w-full md:max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#9a9590]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        wire:model.live.debounce.300ms="productSearch" 
                        type="text" 
                        placeholder="Search products globally..." 
                        class="w-full pl-10 pr-4 py-2 bg-[#0f0f0f] rounded-xl border border-white/10 text-[#e8e4df] placeholder-[#555] focus:outline-none focus:border-[#d4a574] transition-all text-sm"
                    />
                </div>
            </div>

            @if (session()->has('product_status'))
                <div class="p-3 mb-6 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
                    {{ session('product_status') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 text-xs font-mono text-[#9a9590] uppercase tracking-wider">
                            <th class="py-4 px-2">Image</th>
                            <th class="py-4">Product Name</th>
                            <th class="py-4">Category</th>
                            <th class="py-4">Price</th>
                            <th class="py-4">Boutique</th>
                            <th class="py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm text-[#e8e4df]">
                        @forelse($this->allProducts as $product)
                            <tr class="hover:bg-white/[0.02] transition-colors" wire:key="product-{{ $product->tenant_id }}-{{ $product->id }}">
                                <td class="py-3 px-2">
                                    <div class="w-12 h-12 rounded-lg bg-[#0f0f0f] overflow-hidden border border-white/5">
                                        <img src="{{ $product->image_url }}" alt="" class="w-full h-full object-cover">
                                    </div>
                                </td>
                                <td class="py-3 font-semibold text-[#e8e4df]">{{ $product->name }}</td>
                                <td class="py-3 text-[#9a9590] text-xs uppercase tracking-wider font-mono">{{ $product->category_name }}</td>
                                <td class="py-3 text-[#e8e4df] font-mono">€{{ number_format($product->price / 100, 2) }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 rounded-md bg-[#d4a574]/10 text-[#d4a574] text-xs font-semibold border border-[#d4a574]/20">
                                        {{ $product->tenant_name }}
                                    </span>
                                </td>
                                <td class="py-3 text-right space-x-2">
                                    <button type="button"
                                        wire:click="editProduct('{{ $product->tenant_id }}', {{ $product->id }})"
                                        class="inline-block px-3 py-1.5 rounded-lg border border-white/10 text-[#e8e4df] hover:bg-white/10 text-xs font-semibold transition-all">
                                        Edit
                                    </button>
                                    <button type="button"
                                        wire:click="deleteProduct('{{ $product->tenant_id }}', {{ $product->id }})"
                                        wire:confirm="Are you sure you want to delete this product from {{ $product->tenant_name }}?"
                                        class="px-3 py-1.5 rounded-lg border border-red-500/20 text-red-400 hover:bg-red-500/10 text-xs font-semibold transition-all">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <p class="text-[#9a9590]">No products found across any boutique.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @elseif($this->activeTab === 'orders')
        {{-- Order detail slide-over modal --}}
        @if($this->viewingOrder)
            <div class="fixed inset-0 z-50 flex" x-data>
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeOrder"></div>

                {{-- Panel --}}
                <div class="relative ml-auto w-full max-w-lg bg-[#111110] border-l border-white/10 h-full overflow-y-auto flex flex-col shadow-2xl">
                    {{-- Header --}}
                    <div class="flex items-center justify-between p-6 border-b border-white/10 sticky top-0 bg-[#111110] z-10">
                        <div>
                            <p class="text-xs font-mono uppercase tracking-widest text-[#9a9590]">Order #{{ $this->viewingOrder['id'] }}</p>
                            <h2 class="text-xl font-bold text-[#e8e4df] mt-1">{{ $this->viewingOrder['tenant_name'] }}</h2>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider
                                {{ match($this->viewingOrder['status']) {
                                    'paid'      => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20',
                                    'pending'   => 'bg-amber-500/15 text-amber-400 border border-amber-500/20',
                                    'cancelled' => 'bg-red-500/15 text-red-400 border border-red-500/20',
                                    'shipped'   => 'bg-blue-500/15 text-blue-400 border border-blue-500/20',
                                    default     => 'bg-white/10 text-[#9a9590] border border-white/10',
                                } }}">
                                {{ $this->viewingOrder['status'] }}
                            </span>
                            <button type="button" wire:click="closeOrder" class="p-2 rounded-full hover:bg-white/10 text-[#9a9590] hover:text-[#e8e4df] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Customer & Date --}}
                        <div class="bg-[#161615] rounded-2xl border border-white/10 p-5 space-y-3">
                            <h3 class="text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-3">Customer</h3>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#d4a574]/10 flex items-center justify-center text-[#d4a574] font-bold text-sm">
                                    {{ strtoupper(substr($this->viewingOrder['email'], 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[#e8e4df]">{{ $this->viewingOrder['email'] }}</p>
                                    <p class="text-xs text-[#9a9590] font-mono">{{ $this->viewingOrder['created_at'] }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Order Items --}}
                        <div class="bg-[#161615] rounded-2xl border border-white/10 p-5">
                            <h3 class="text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-4">Items</h3>
                            <div class="space-y-3">
                                @foreach($this->viewingOrder['items'] as $item)
                                    <div class="flex justify-between items-center py-2 border-b border-white/5 last:border-0">
                                        <div>
                                            <p class="text-sm font-semibold text-[#e8e4df]">{{ $item['name'] }}</p>
                                            <p class="text-xs text-[#9a9590] font-mono">Qty: {{ $item['quantity'] }}</p>
                                        </div>
                                        <span class="text-sm font-mono font-bold text-[#d4a574]">
                                            €{{ number_format($item['price'] / 100, 2) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between items-center pt-4 mt-2 border-t border-white/10">
                                <span class="text-sm font-bold text-[#e8e4df]">Total</span>
                                <span class="text-xl font-bold font-mono text-[#d4a574]">€{{ number_format($this->viewingOrder['total_amount'] / 100, 2) }}</span>
                            </div>
                        </div>

                        {{-- Shipping Address --}}
                        @if($this->viewingOrder['shipping_address'])
                        <div class="bg-[#161615] rounded-2xl border border-white/10 p-5">
                            <h3 class="text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-3">Shipping Address</h3>
                            @php $addr = $this->viewingOrder['shipping_address']; @endphp
                            <p class="text-sm text-[#e8e4df]">{{ ($addr['first_name'] ?? '') . ' ' . ($addr['last_name'] ?? '') }}</p>
                            <p class="text-sm text-[#9a9590]">{{ $addr['address'] ?? '' }}</p>
                            <p class="text-sm text-[#9a9590]">{{ ($addr['zip_code'] ?? '') . ' ' . ($addr['city'] ?? '') }}</p>
                        </div>
                        @endif

                        {{-- Payment Info --}}
                        <div class="bg-[#161615] rounded-2xl border border-white/10 p-5 space-y-2">
                            <h3 class="text-xs font-mono uppercase tracking-widest text-[#9a9590] mb-3">Payment</h3>
                            <div class="flex justify-between text-sm">
                                <span class="text-[#9a9590]">Method</span>
                                <span class="text-[#e8e4df] font-semibold uppercase tracking-wider">{{ $this->viewingOrder['payment_method'] }}</span>
                            </div>
                            @if($this->viewingOrder['transaction_id'])
                            <div class="flex justify-between text-sm">
                                <span class="text-[#9a9590]">Transaction ID</span>
                                <span class="text-[#e8e4df] font-mono text-xs truncate max-w-[200px]" title="{{ $this->viewingOrder['transaction_id'] }}">{{ $this->viewingOrder['transaction_id'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Orders List --}}
        <div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <h2 class="text-xl font-bold text-[#e8e4df] font-accent">Order History</h2>
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    {{-- Status filter --}}
                    <select wire:model.live="orderStatusFilter" class="bg-[#0f0f0f] border border-white/10 text-[#e8e4df] text-sm rounded-xl px-4 py-2.5 focus:ring-[#d4a574] focus:border-[#d4a574] outline-none transition-colors cursor-pointer appearance-none">
                        <option value="all">All Statuses</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="shipped">Shipped</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    {{-- Search --}}
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#9a9590]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input wire:model.live.debounce.300ms="orderSearch" type="text" placeholder="Search by email or ID..." class="w-full sm:w-72 pl-10 pr-4 py-2.5 bg-[#0f0f0f] rounded-xl border border-white/10 text-[#e8e4df] placeholder-[#555] focus:outline-none focus:border-[#d4a574] transition-all text-sm" />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 text-xs font-mono text-[#9a9590] uppercase tracking-wider">
                            <th class="py-4">#ID</th>
                            <th class="py-4">Boutique</th>
                            <th class="py-4">Customer</th>
                            <th class="py-4">Items</th>
                            <th class="py-4">Total</th>
                            <th class="py-4">Status</th>
                            <th class="py-4">Date</th>
                            <th class="py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm text-[#e8e4df]">
                        @forelse($this->allOrders as $order)
                            <tr class="hover:bg-white/[0.02] transition-colors" wire:key="order-{{ $order->tenant_id }}-{{ $order->id }}">
                                <td class="py-4 font-mono text-[#9a9590] text-xs">#{{ $order->id }}</td>
                                <td class="py-4">
                                    <span class="px-2 py-1 rounded-md bg-[#d4a574]/10 text-[#d4a574] text-xs font-semibold border border-[#d4a574]/20">
                                        {{ $order->tenant_name }}
                                    </span>
                                </td>
                                <td class="py-4 text-[#e8e4df] text-sm">{{ $order->email }}</td>
                                <td class="py-4 text-[#9a9590] text-xs font-mono">{{ $order->items_count }} item{{ $order->items_count !== 1 ? 's' : '' }}</td>
                                <td class="py-4 font-mono font-bold text-[#d4a574]">€{{ number_format($order->total_amount / 100, 2) }}</td>
                                <td class="py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider
                                        {{ match($order->status) {
                                            'paid'      => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20',
                                            'pending'   => 'bg-amber-500/15 text-amber-400 border border-amber-500/20',
                                            'cancelled' => 'bg-red-500/15 text-red-400 border border-red-500/20',
                                            'shipped'   => 'bg-blue-500/15 text-blue-400 border border-blue-500/20',
                                            default     => 'bg-white/10 text-[#9a9590] border border-white/10',
                                        } }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="py-4 text-[#9a9590] text-xs font-mono">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                                </td>
                                <td class="py-4 text-right">
                                    <button type="button"
                                        wire:click="viewOrder('{{ $order->tenant_id }}', {{ $order->id }})"
                                        class="px-3 py-1.5 rounded-lg border border-white/10 text-[#e8e4df] hover:bg-white/10 text-xs font-semibold transition-all">
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-16 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-[#9a9590]/40 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-[#9a9590]">No orders found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

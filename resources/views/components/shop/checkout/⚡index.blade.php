<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use App\Services\CartService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.app')] class extends Component
{
    #[Validate('required|email:rfc')]
    public string $email = '';

    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required|string|max:255')]
    public string $address = '';

    #[Validate('required|string|max:255')]
    public string $city = '';

    #[Validate('required|string|max:20')]
    public string $zip_code = '';

    #[Validate('required|in:credit_card,ideal,stripe')]
    public string $payment_method = 'stripe';

    public function mount()
    {
        $user = auth()->user();

        // Only pre-fill from the authenticated user if they are a customer.
        // Admins and staff may browse the storefront but should not have
        // their own credentials silently injected into the checkout form.
        if ($user && $user->role === \App\Enums\Role::CUSTOMER) {
            $this->email = $user->email;
            $parts = explode(' ', $user->name);
            $this->first_name = array_shift($parts);
            $this->last_name = implode(' ', $parts);
        }
    }

    public function processCheckout()
    {
        $cartService = app(CartService::class);
        $this->validate();

        $items = $cartService->getCartDetails();
        
        if (empty($items)) {
            session()->flash('error', 'Your cart is empty.');
            return;
        }

        try {
            DB::beginTransaction();

            // Re-validate stock availability for all real variants before creating the order
            foreach ($items as $item) {
                if ($item['id'] === 999) continue; // skip mock variant

                $variant = ProductVariant::find($item['id']);
                if (!$variant) {
                    session()->flash('error', "Product \"" . $item['name'] . "\" is no longer available.");
                    DB::rollBack();
                    return;
                }
                if ($variant->stock_available < $item['quantity']) {
                    $available = $variant->stock_available;
                    session()->flash('error',
                        $available > 0
                            ? "Sorry, only {$available} unit(s) of \"" . $item['name'] . "\" are available. Please update your cart."
                            : "Sorry, \"" . $item['name'] . "\" is now out of stock. Please remove it from your cart."
                    );
                    DB::rollBack();
                    return;
                }
            }

            $totalAmount = 0;
            $lineItems = [];
            foreach ($items as $item) {
                $unitAmount = (int) round($item['price'] * 100);
                $totalAmount += $unitAmount * $item['quantity'];
                
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $item['name'] . ' (' . $item['variant_value'] . ')',
                        ],
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => $item['quantity'],
                ];
            }

            // Create Order
            $order = Order::create([
                'user_id' => auth()->id(),
                'email' => $this->email,
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'shipping_address' => [
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'address' => $this->address,
                    'city' => $this->city,
                    'zip_code' => $this->zip_code,
                ],
                'billing_address' => [
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'address' => $this->address,
                    'city' => $this->city,
                    'zip_code' => $this->zip_code,
                ],
                'payment_method' => $this->payment_method,
                'transaction_id' => 'txn_dummy_' . uniqid(),
            ]);

            // Create Order Items
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item['id'] === 999 ? null : $item['id'],
                    'name' => $item['name'] . ' (' . $item['variant_value'] . ')',
                    'price' => (int) round($item['price'] * 100),
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            // Clear the cart
            $cartService->clear();

            // Stripe Checkout Integration
            $stripe = app()->bound(\Stripe\StripeClient::class)
                ? app(\Stripe\StripeClient::class)
                : new \Stripe\StripeClient(config('services.stripe.secret') ?: env('STRIPE_SECRET'));

            $paymentMethodTypes = ['card'];
            if ($this->payment_method === 'ideal') {
                $paymentMethodTypes = ['ideal'];
            } elseif ($this->payment_method === 'stripe') {
                // If they specifically choose "Stripe Checkout", we let Stripe present all enabled methods
                $paymentMethodTypes = ['card', 'ideal'];
            }

            $checkout_session = $stripe->checkout->sessions->create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('shop.checkout.success', ['order' => $order->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('shop.checkout.cancel', ['order' => $order->id]),
                'payment_method_types' => $paymentMethodTypes,
                'customer_email' => $this->email,
                'client_reference_id' => $order->id,
            ]);

            $order->update(['transaction_id' => $checkout_session->id]);

            $this->redirect($checkout_session->url);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong while processing your order: ' . $e->getMessage());
        }
    }
};
?>

<div class="relative min-h-[85dvh] py-16">
    <div class="absolute inset-0 bg-radial-at-t from-[#d4a574]/5 via-transparent to-transparent opacity-60 pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-6 relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <!-- Checkout Form -->
        <div class="lg:col-span-7">
            <h1 class="text-4xl font-bold mb-8 text-[#e8e4df]">Checkout</h1>

            @if(session()->has('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit="processCheckout" class="space-y-8">
                
                <!-- Contact Info -->
                <div class="bg-[#161615] rounded-[32px] border border-white/5 p-8">
                    <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Contact Information</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">Email Address</label>
                            <input type="email" wire:model.blur="email" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Please enter a valid email address (e.g., name@example.com)" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none" placeholder="you@example.com" required>
                            @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="bg-[#161615] rounded-[32px] border border-white/5 p-8">
                    <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Shipping Address</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">First Name</label>
                            <input type="text" wire:model="first_name" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none">
                            @error('first_name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">Last Name</label>
                            <input type="text" wire:model="last_name" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none">
                            @error('last_name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">Address</label>
                            <input type="text" wire:model="address" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none" placeholder="Street name and house number">
                            @error('address') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">City</label>
                            <input type="text" wire:model="city" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none">
                            @error('city') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#9a9590] mb-2">Postal Code</label>
                            <input type="text" wire:model="zip_code" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[#e8e4df] focus:border-[#d4a574] focus:ring-1 focus:ring-[#d4a574] transition-colors outline-none">
                            @error('zip_code') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-[#161615] rounded-[32px] border border-white/5 p-8">
                    <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Payment Method</h2>
                    <div class="space-y-3">
                        <label class="flex items-center gap-4 p-4 rounded-xl border border-white/10 cursor-pointer hover:bg-white/5 transition-colors {{ $payment_method === 'stripe' ? 'bg-white/5 border-[#d4a574]' : '' }}">
                            <input type="radio" wire:model="payment_method" value="stripe" class="text-[#d4a574] focus:ring-[#d4a574] bg-transparent border-white/20">
                            <div class="flex flex-col">
                                <span class="text-[#e8e4df] font-semibold">Stripe Checkout</span>
                                <span class="text-xs text-[#9a9590]">Pay with Credit Card, iDEAL, or PayPal securely via Stripe.</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-4 p-4 rounded-xl border border-white/10 cursor-pointer hover:bg-white/5 transition-colors {{ $payment_method === 'ideal' ? 'bg-white/5 border-[#d4a574]' : '' }}">
                            <input type="radio" wire:model="payment_method" value="ideal" class="text-[#d4a574] focus:ring-[#d4a574] bg-transparent border-white/20">
                            <span class="text-[#e8e4df] font-semibold">iDEAL</span>
                        </label>
                        <label class="flex items-center gap-4 p-4 rounded-xl border border-white/10 cursor-pointer hover:bg-white/5 transition-colors {{ $payment_method === 'credit_card' ? 'bg-white/5 border-[#d4a574]' : '' }}">
                            <input type="radio" wire:model="payment_method" value="credit_card" class="text-[#d4a574] focus:ring-[#d4a574] bg-transparent border-white/20">
                            <span class="text-[#e8e4df] font-semibold">Credit Card</span>
                        </label>
                    </div>
                    @error('payment_method') <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full py-4 rounded-full bg-[#d4a574] text-[#0f0f0f] font-bold text-lg hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="processCheckout">Complete Order</span>
                    <span wire:loading wire:target="processCheckout">Processing...</span>
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-5">
            <div class="bg-[#161615] rounded-[32px] border border-white/5 p-8 sticky top-32">
                <h2 class="text-xl font-bold text-[#e8e4df] mb-6">Order Summary</h2>
                
                <div class="space-y-4 mb-6">
                    @php $items = app(\App\Services\CartService::class)->getCartDetails(); @endphp
                    @foreach($items as $item)
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-xs text-[#d4a574] font-semibold">{{ $item['quantity'] }}</span>
                                <span class="text-[#e8e4df]">{{ $item['name'] }}</span>
                            </div>
                            <span class="text-[#9a9590]">€ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
                
                <div class="border-t border-white/5 pt-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-[#9a9590]">Subtotal</span>
                        <span class="text-[#e8e4df]">€ {{ number_format(app(\App\Services\CartService::class)->getTotal(), 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#9a9590]">Shipping</span>
                        <span class="text-[#d4a574]">Free</span>
                    </div>
                </div>

                <div class="border-t border-white/5 mt-6 pt-6 flex justify-between items-end">
                    <span class="text-[#e8e4df] font-bold">Total</span>
                    <span class="text-3xl font-bold text-[#e8e4df]">
                        € {{ number_format(app(\App\Services\CartService::class)->getTotal(), 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>

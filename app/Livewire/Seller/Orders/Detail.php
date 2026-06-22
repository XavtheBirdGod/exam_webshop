<?php

namespace App\Livewire\Seller\Orders;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Detail extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        // Load relationships
        $this->order->load(['items.productVariant.product', 'paymentLogs']);
    }

    public function render()
    {
        return view('components.seller.orders.⚡detail');
    }
}

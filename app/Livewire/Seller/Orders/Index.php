<?php

namespace App\Livewire\Seller\Orders;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public function with(): array
    {
        return [
            'orders' => Order::latest()->get(),
        ];
    }

    public function render()
    {
        return view('components.seller.orders.⚡index');
    }
}

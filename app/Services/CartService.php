<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected function getSessionKey(): string
    {
        return 'cart_' . (tenant('id') ?? 'central');
    }

    public function getItems(): array
    {
        return Session::get($this->getSessionKey(), []);
    }

    public function getCount(): int
    {
        return array_sum($this->getItems());
    }

    public function add(int $variantId, int $quantity = 1): void
    {
        $items = $this->getItems();
        $currentQuantity = $items[$variantId] ?? 0;
        
        $newQuantity = $currentQuantity + $quantity;
        
        $variant = ProductVariant::find($variantId);
        
        // Support for mock variant from Phase 4
        if (!$variant && $variantId === 999) {
            $items[$variantId] = $newQuantity;
        } else {
            if ($variant && $variant->stock_available >= $newQuantity) {
                $items[$variantId] = $newQuantity;
            } elseif ($variant) {
                throw new \Exception("Only {$variant->stock_available} items available in stock.");
            } else {
                throw new \Exception("Product variant not found.");
            }
        }

        Session::put($this->getSessionKey(), $items);
    }

    public function updateQuantity(int $variantId, int $quantity): void
    {
        $items = $this->getItems();
        
        if ($quantity <= 0) {
            unset($items[$variantId]);
            Session::put($this->getSessionKey(), $items);
            return;
        }

        $variant = ProductVariant::find($variantId);
        
        if (!$variant && $variantId === 999) {
            $items[$variantId] = $quantity;
        } else {
            if ($variant && $variant->stock_available >= $quantity) {
                $items[$variantId] = $quantity;
            } elseif ($variant) {
                throw new \Exception("Only {$variant->stock_available} items available in stock.");
            } else {
                throw new \Exception("Product variant not found.");
            }
        }

        Session::put($this->getSessionKey(), $items);
    }

    public function remove(int $variantId): void
    {
        $items = $this->getItems();
        unset($items[$variantId]);
        Session::put($this->getSessionKey(), $items);
    }

    public function clear(): void
    {
        Session::forget($this->getSessionKey());
    }

    public function getTotal(): float
    {
        $items = $this->getItems();
        $total = 0;

        if (empty($items)) return 0;

        $variants = ProductVariant::with('product')->whereIn('id', array_keys($items))->get();
        
        foreach ($items as $variantId => $quantity) {
            if ($variantId === 999) {
                $total += 2500 * $quantity;
                continue;
            }

            $variant = $variants->firstWhere('id', $variantId);
            if ($variant && $variant->product) {
                $price = $variant->product->price + $variant->price_modifier;
                $total += $price * $quantity;
            }
        }

        return $total / 100;
    }

    public function getCartDetails(): array
    {
        $items = $this->getItems();
        $details = [];

        if (empty($items)) return $details;

        $variants = ProductVariant::with(['product.images'])->whereIn('id', array_keys($items))->get();

        foreach ($items as $variantId => $quantity) {
            if ($variantId === 999) {
                $details[] = [
                    'id' => 999,
                    'name' => 'Signature Ritual Collection',
                    'variant_name' => 'Size',
                    'variant_value' => 'Default',
                    'price' => 25.00,
                    'quantity' => $quantity,
                    'image' => null,
                    'stock_available' => 10,
                ];
                continue;
            }

            $variant = $variants->firstWhere('id', $variantId);
            if ($variant && $variant->product) {
                $price = ($variant->product->price + $variant->price_modifier) / 100;
                $imageUrl = $variant->product->images->first()?->path ?? '';
                
                $details[] = [
                    'id' => $variant->id,
                    'name' => $variant->product->name,
                    'variant_name' => $variant->name,
                    'variant_value' => $variant->value,
                    'price' => $price,
                    'quantity' => $quantity,
                    'image' => $imageUrl,
                    'stock_available' => $variant->stock_available,
                ];
            }
        }

        return $details;
    }
}

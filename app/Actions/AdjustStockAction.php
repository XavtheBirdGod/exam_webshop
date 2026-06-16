<?php

namespace App\Actions;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class AdjustStockAction
{
    /**
     * Execute the adjust stock action.
     */
    public function execute(ProductVariant $variant, int $quantity, string $type, string $description = ''): ProductVariant
    {
        return DB::transaction(function () use ($variant, $quantity, $type, $description) {
            if ($type === 'in') {
                $variant->stock_on_hand += $quantity;
            } elseif ($type === 'out') {
                $variant->stock_on_hand -= $quantity;
            } elseif ($type === 'reserve') {
                $variant->stock_reserved += $quantity;
            } elseif ($type === 'release') {
                $variant->stock_reserved -= $quantity;
            } elseif ($type === 'sale') {
                $variant->stock_on_hand -= $quantity;
                $variant->stock_reserved -= $quantity;
            }

            // Recalculate available stock
            $variant->stock_available = $variant->stock_on_hand - $variant->stock_reserved;
            
            // Ensure stock levels do not drop below zero
            if ($variant->stock_on_hand < 0) {
                $variant->stock_on_hand = 0;
            }
            if ($variant->stock_reserved < 0) {
                $variant->stock_reserved = 0;
            }
            if ($variant->stock_available < 0) {
                $variant->stock_available = 0;
            }
            
            $variant->save();

            // Log stock movement
            $variant->stockMovements()->create([
                'type' => $type,
                'quantity' => $quantity,
                'description' => $description ?: ucfirst($type) . ' adjustment of ' . $quantity . ' units',
            ]);

            return $variant;
        });
    }
}

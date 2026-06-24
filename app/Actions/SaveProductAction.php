<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaveProductAction
{
    /**
     * Execute the save product action.
     */
    public function execute(array $data, ?Product $product = null): Product
    {
        return DB::transaction(function () use ($data, $product) {
            $isNew = !$product;
            
            if ($isNew) {
                $product = new Product();
            }

            $product->name = $data['name'];
            $product->slug = $data['slug'] ?? Str::slug($data['name']);
            $product->description = $data['description'] ?? null;
            $product->price = $data['price'];
            $product->category_id = $data['category_id'] ?? null;
            $product->status = $data['status'] ?? 'active';
            $product->featured = $data['featured'] ?? false;
            $product->save();

            // Handle variants
            if (isset($data['variants'])) {
                $variantIds = [];
                
                foreach ($data['variants'] as $variantData) {
                    $variant = null;
                    
                    if (isset($variantData['id']) && !$isNew) {
                        $variant = ProductVariant::find($variantData['id']);
                    }
                    
                    if (!$variant) {
                        $variant = new ProductVariant();
                    }

                    $variant->product_id = $product->id;
                    $variant->name = $variantData['name'];
                    $variant->value = $variantData['value'];
                    $variant->sku = $variantData['sku'];
                    $variant->price_modifier = $variantData['price_modifier'] ?? 0;
                    
                    // Manage stock levels
                    $stockOnHand = $variantData['stock_on_hand'] ?? 0;
                    $stockReserved = $variantData['stock_reserved'] ?? 0;
                    $stockAvailable = $stockOnHand - $stockReserved;
                    
                    $oldStockOnHand = $variant->stock_on_hand ?? 0;
                    
                    $variant->stock_on_hand = $stockOnHand;
                    $variant->stock_reserved = $stockReserved;
                    $variant->stock_available = $stockAvailable;
                    $variant->save();
                    
                    $variantIds[] = $variant->id;

                    // Log stock movement if stock changed
                    if ($stockOnHand !== $oldStockOnHand) {
                        $diff = $stockOnHand - $oldStockOnHand;
                        $variant->stockMovements()->create([
                            'type' => $diff > 0 ? 'in' : 'out',
                            'quantity' => $diff,
                            'description' => $isNew ? 'Initial stock seeding' : 'Stock level manual adjustment',
                        ]);
                    }
                }
                
                // Delete variants not in the updated list
                if (!$isNew) {
                    $product->variants()->whereNotIn('id', $variantIds)->delete();
                }
            }

            // Handle image deletions
            if (!empty($data['delete_image_ids'])) {
                foreach ($data['delete_image_ids'] as $imageId) {
                    $img = $product->images()->find($imageId);
                    if ($img) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($img->path);
                        $img->delete();
                    }
                }
            }

            // Handle new image uploads
            if (!empty($data['new_image_paths'])) {
                $hasPrimary = $product->images()->where('is_primary', true)->exists();

                foreach ($data['new_image_paths'] as $index => $path) {
                    $product->images()->create([
                        'path'       => $path,
                        // Make the first uploaded image the primary if none exists yet
                        'is_primary' => (!$hasPrimary && $index === 0),
                    ]);
                    $hasPrimary = true;
                }
            }

            return $product;
        });
    }
}

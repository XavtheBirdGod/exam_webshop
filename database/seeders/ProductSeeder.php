<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Category
        $category = Category::firstOrCreate(
            ['slug' => 'body-care'],
            [
                'name' => 'Body Care',
                'description' => 'Luxurious body care products',
            ]
        );

        // Product 1
        $product = Product::firstOrCreate(
            ['slug' => 'ritual-of-sakura-body-cream'],
            [
                'category_id' => $category->id,
                'name' => 'The Ritual of Sakura Body Cream',
                'description' => 'Best-selling body cream with the scent of cherry blossom and rice milk.',
                'price' => 2290, // 22.90 in cents
                'status' => 'active',
            ]
        );

        // Variant 1
        ProductVariant::firstOrCreate(
            ['sku' => 'SAKURA-CREAM-220'],
            [
                'product_id' => $product->id,
                'name' => 'Size',
                'value' => '220 ml',
                'price_modifier' => 0,
                'stock_on_hand' => 50,
                'stock_available' => 50,
            ]
        );

        // Variant 2
        ProductVariant::firstOrCreate(
            ['sku' => 'SAKURA-CREAM-REFILL'],
            [
                'product_id' => $product->id,
                'name' => 'Size',
                'value' => '220 ml (Refill)',
                'price_modifier' => -300, // 3.00 cheaper
                'stock_on_hand' => 100,
                'stock_available' => 100,
            ]
        );
        
        // Product 2
        $product2 = Product::firstOrCreate(
            ['slug' => 'ritual-of-jing-pillow-mist'],
            [
                'category_id' => $category->id,
                'name' => 'The Ritual of Jing Pillow Mist',
                'description' => 'Deep sleep pillow and body mist.',
                'price' => 1990, // 19.90 in cents
                'status' => 'active',
            ]
        );

        ProductVariant::firstOrCreate(
            ['sku' => 'JING-MIST-50'],
            [
                'product_id' => $product2->id,
                'name' => 'Size',
                'value' => '50 ml',
                'price_modifier' => 0,
                'stock_on_hand' => 20,
                'stock_available' => 20,
            ]
        );
    }
}

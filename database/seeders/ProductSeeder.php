<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // First, clear existing products for a clean seed?
        // Let's not delete existing, just use updateOrCreate to avoid duplicates
        
        $products = [
            ['cat' => 'Limited Edition', 'col' => 'The Ritual of Seshen', 'name' => 'Foaming Shower Gel (200ml)', 'price' => 10.90, 'img' => 'seshen-body-foam.png'],
            ['cat' => 'Limited Edition', 'col' => 'The Ritual of Seshen', 'name' => 'Body Gel (200ml)', 'price' => 24.90, 'img' => 'seshen-body-gel.png'],
            ['cat' => 'Limited Edition', 'col' => 'The Ritual of Seshen', 'name' => 'Eau de Parfum (50ml)', 'price' => 50.90, 'img' => 'seshen-eau-parfum.png'],
            ['cat' => 'Limited Edition', 'col' => 'The Ritual of Seshen', 'name' => 'Large Gift Set', 'price' => 59.90, 'img' => 'seshen-large-giftset.png'],
            ['cat' => 'Seasonal', 'col' => 'The Ritual of Ayurveda', 'name' => 'Foaming Shower Gel (200ml)', 'price' => 10.90, 'img' => 'ayurveda-body-foam.png'],
            ['cat' => 'Seasonal', 'col' => 'The Ritual of Ayurveda', 'name' => 'Body Cream (220ml)', 'price' => 24.90, 'img' => 'ayurveda-body-mousse.png'],
            ['cat' => 'Seasonal', 'col' => 'The Ritual of Ayurveda', 'name' => 'Large Gift Set', 'price' => 49.90, 'img' => null],
            ['cat' => 'Seasonal', 'col' => 'Skincare Collection', 'name' => 'Namaste Anti-Aging Face Oil (30ml)', 'price' => 35.90, 'img' => 'skincare-anti-aging.png'],
            ['cat' => 'Seasonal', 'col' => 'Men\'s Collection', 'name' => 'Sport Cooling Shower Gel (200ml)', 'price' => 10.90, 'img' => 'sport-cooling-shower-gel.png'],
            ['cat' => 'Seasonal', 'col' => 'Private Collection', 'name' => 'Velvet Oudh Shower Foam (200ml)', 'price' => 10.90, 'img' => 'velvet-body-foam.png'],
            ['cat' => 'Seasonal', 'col' => 'Private Collection', 'name' => 'Sweet Jasmine Hand Soap (300ml)', 'price' => 15.90, 'img' => 'sweet-jasmine-hand-wash.png'],
            ['cat' => 'Seasonal', 'col' => 'Private Collection', 'name' => 'Precious Amber Room Spray (500ml)', 'price' => 39.90, 'img' => 'precious-amber-room-spray.png'],
            ['cat' => 'Seasonal', 'col' => 'Private Collection', 'name' => 'Suede Vanilla Gift Set L', 'price' => 65.90, 'img' => 'suede-vanilla-large-gift.webp'],
            ['cat' => 'Bestselling', 'col' => 'The Ritual of Yozakura', 'name' => 'Foaming Shower Gel (200ml)', 'price' => 10.90, 'img' => 'yozakura-body-foam.webp'],
            ['cat' => 'Bestselling', 'col' => 'The Ritual of Yozakura', 'name' => 'Whipped Body Cream (220ml)', 'price' => 24.90, 'img' => 'yozakura-body-cream.webp'],
            ['cat' => 'Bestselling', 'col' => 'The Ritual of Sakura', 'name' => 'Foaming Shower Gel (200ml)', 'price' => 10.90, 'img' => 'sakura-body-foam.png'],
            ['cat' => 'Bestselling', 'col' => 'The Ritual of Sakura', 'name' => 'Body Cream (220ml)', 'price' => 24.90, 'img' => 'sakura-body-cream.png'],
            ['cat' => 'Bestselling', 'col' => 'The Ritual of Sakura', 'name' => 'Large Gift Set', 'price' => 49.90, 'img' => 'sakura-large-giftset.png'],
            ['cat' => 'Bestselling', 'col' => 'Amsterdam Collection', 'name' => 'Foaming Shower Gel (200ml)', 'price' => 10.90, 'img' => 'amsterdam-body-foam.webp'],
            ['cat' => 'Bestselling', 'col' => 'Amsterdam Collection', 'name' => 'Body Mist (50ml)', 'price' => 23.90, 'img' => 'amsterdam-body-mist.webp'],
            ['cat' => 'Signature', 'col' => 'The Ritual of Karma', 'name' => 'Foaming Shower Gel (200ml)', 'price' => 10.90, 'img' => 'karma-body-foam.webp'],
            ['cat' => 'Signature', 'col' => 'The Ritual of Karma', 'name' => 'Shimmering Body Oil (100ml)', 'price' => 22.90, 'img' => 'karma-body-oil.webp'],
            ['cat' => 'Signature', 'col' => 'The Ritual of Jing', 'name' => 'Pillow & Body Mist (50ml)', 'price' => 23.90, 'img' => 'jing-pillow-mist.png'],
            ['cat' => 'Signature', 'col' => 'The Ritual of Mehr', 'name' => 'Energising Hair & Body Mist', 'price' => 23.90, 'img' => null],
            ['cat' => 'Signature', 'col' => 'The Ritual of Hammam', 'name' => 'Hot Scrub (125g)', 'price' => 17.90, 'img' => 'hammam-body-scrub.webp'],
        ];

        foreach ($products as $item) {
            // Ensure Category exists
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($item['cat'])],
                ['name' => $item['cat'], 'description' => $item['cat'] . ' Collection']
            );

            // Full product name
            $fullName = $item['col'] . ' ' . $item['name'];
            $slug = Str::slug($fullName);

            // Create or update Product
            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'name' => $fullName,
                    'description' => 'Experience the luxury of ' . $item['col'] . ' with this ' . $item['name'] . '.',
                    'price' => (int)($item['price'] * 100),
                    'status' => 'active',
                ]
            );

            // Create Variant (Standard)
            ProductVariant::updateOrCreate(
                ['sku' => strtoupper(Str::slug($item['col'] . '-' . $item['name']))],
                [
                    'product_id' => $product->id,
                    'name' => 'Standard',
                    'value' => 'Standard',
                    'price_modifier' => 0,
                    'stock_on_hand' => 100,
                    'stock_available' => 100,
                ]
            );

            // Handle Image
            if ($item['img']) {
                $sourcePath = base_path('images/' . $item['img']);
                if (File::exists($sourcePath)) {
                    // Create storage directory if it doesn't exist
                    Storage::disk('public')->makeDirectory('products');
                    
                    // Destination path relative to disk root
                    $destPath = 'products/' . $item['img'];
                    
                    // Copy file
                    Storage::disk('public')->put($destPath, File::get($sourcePath));
                    
                    // Update or Create ProductImage
                    ProductImage::updateOrCreate(
                        ['product_id' => $product->id, 'is_primary' => true],
                        [
                            'path' => $destPath,
                        ]
                    );
                }
            }
        }
    }
}

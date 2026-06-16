<?php

namespace App\Livewire\Forms;

use App\Actions\SaveProductAction;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Form;

class ProductForm extends Form
{
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public $price = ''; // Keep raw string/float for input representation
    public ?int $category_id = null;
    public string $status = 'active';
    public bool $featured = false;
    public array $variants = [];

    /**
     * Define validation rules.
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:active,inactive,draft',
            'featured' => 'required|boolean',
            'variants' => 'required|array|min:1',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.value' => 'required|string|max:255',
            'variants.*.sku' => 'required|string|max:255|distinct',
            'variants.*.price_modifier' => 'required|numeric',
            'variants.*.stock_on_hand' => 'required|integer|min:0',
        ];
    }

    /**
     * Store the product using the action.
     */
    public function store(SaveProductAction $action): Product
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug ?: Str::slug($this->name),
            'description' => $this->description,
            'price' => (int) round($this->price * 100), // convert to cents
            'category_id' => $this->category_id,
            'status' => $this->status,
            'featured' => $this->featured,
            'variants' => array_map(function ($variant) {
                return [
                    'id' => $variant['id'] ?? null,
                    'name' => $variant['name'],
                    'value' => $variant['value'],
                    'sku' => $variant['sku'],
                    'price_modifier' => (int) round($variant['price_modifier'] * 100), // convert to cents
                    'stock_on_hand' => (int) $variant['stock_on_hand'],
                    'stock_reserved' => $variant['stock_reserved'] ?? 0,
                ];
            }, $this->variants),
        ];

        return $action->execute($data);
    }

    /**
     * Populate form fields from an existing product.
     */
    public function fillFromProduct(Product $product): void
    {
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->description = $product->description ?? '';
        $this->price = $product->price / 100;
        $this->category_id = $product->category_id;
        $this->status = $product->status;
        $this->featured = (bool) $product->featured;
        
        $this->variants = $product->variants->map(function ($v) {
            return [
                'id' => $v->id,
                'name' => $v->name,
                'value' => $v->value,
                'sku' => $v->sku,
                'price_modifier' => $v->price_modifier / 100,
                'stock_on_hand' => $v->stock_on_hand,
                'stock_reserved' => $v->stock_reserved,
            ];
        })->toArray();
    }
}

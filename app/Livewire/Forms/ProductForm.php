<?php

namespace App\Livewire\Forms;

use App\Actions\SaveProductAction;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Form;
use Livewire\WithFileUploads;

class ProductForm extends Form
{
    use WithFileUploads;

    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public $price = ''; // Keep raw string/float for input representation
    public ?int $category_id = null;
    public string $status = 'active';
    public bool $featured = false;
    public array $variants = [];

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $images = [];

    /** Paths of images already saved (used on edit to show previews) */
    public array $existingImages = [];

    /** IDs of existing images the user wants to delete */
    public array $deleteImageIds = [];

    /**
     * Define validation rules.
     */
    protected function rules(): array
    {
        return [
            'name'                        => 'required|string|max:255',
            'slug'                        => 'nullable|string|max:255',
            'description'                 => 'nullable|string',
            'price'                       => 'required|numeric|min:0',
            'category_id'                 => 'nullable|exists:categories,id',
            'status'                      => 'required|in:active,inactive,draft',
            'featured'                    => 'required|boolean',
            'variants'                    => 'required|array|min:1',
            'variants.*.name'             => 'required|string|max:255',
            'variants.*.value'            => 'required|string|max:255',
            'variants.*.sku'              => 'required|string|max:255|distinct',
            'variants.*.price_modifier'   => 'required|numeric',
            'variants.*.stock_on_hand'    => 'required|integer|min:0',
            'images.*'                    => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
        ];
    }

    /**
     * Store the product using the action.
     */
    public function store(SaveProductAction $action, ?Product $product = null, array $extraImagePaths = []): Product
    {
        $this->validate();

        // Store uploaded images to the public disk and collect their paths
        $uploadedPaths = $extraImagePaths;
        foreach ($this->images as $image) {
            $path = $image->store('products', 'public');
            $uploadedPaths[] = $path;
        }

        $data = [
            'name'            => $this->name,
            'slug'            => $this->slug ?: Str::slug($this->name),
            'description'     => $this->description,
            'price'           => (int) round($this->price * 100),
            'category_id'     => $this->category_id,
            'status'          => $this->status,
            'featured'        => $this->featured,
            'new_image_paths' => $uploadedPaths,
            'delete_image_ids'=> $this->deleteImageIds,
            'variants'        => array_map(function ($variant) {
                return [
                    'id'             => $variant['id'] ?? null,
                    'name'           => $variant['name'],
                    'value'          => $variant['value'],
                    'sku'            => $variant['sku'],
                    'price_modifier' => (int) round($variant['price_modifier'] * 100),
                    'stock_on_hand'  => (int) $variant['stock_on_hand'],
                    'stock_reserved' => $variant['stock_reserved'] ?? 0,
                ];
            }, $this->variants),
        ];

        return $action->execute($data, $product);
    }

    /**
     * Populate form fields from an existing product.
     */
    public function fillFromProduct(Product $product, ?string $tenantAssetBaseUrl = null): void
    {
        $this->name        = $product->name;
        $this->slug        = $product->slug;
        $this->description = $product->description ?? '';
        $this->price       = $product->price / 100;
        $this->category_id = $product->category_id;
        $this->status      = $product->status;
        $this->featured    = (bool) $product->featured;

        // Load existing images for preview in the edit form
        // If a custom base URL is provided (e.g. from platform admin dashboard),
        // use it to build direct tenant-domain URLs instead of tenant_asset().
        $this->existingImages = $product->images->map(function ($img) use ($tenantAssetBaseUrl) {
            if ($tenantAssetBaseUrl) {
                $url = rtrim($tenantAssetBaseUrl, '/') . '/tenancy/assets/' . ltrim($img->path, '/');
            } else {
                $url = tenant_asset($img->path);
            }
            return [
                'id'         => $img->id,
                'url'        => $url,
                'is_primary' => $img->is_primary,
            ];
        })->toArray();

        $this->variants = $product->variants->map(function ($v) {
            return [
                'id'             => $v->id,
                'name'           => $v->name,
                'value'          => $v->value,
                'sku'            => $v->sku,
                'price_modifier' => $v->price_modifier / 100,
                'stock_on_hand'  => $v->stock_on_hand,
                'stock_reserved' => $v->stock_reserved,
            ];
        })->toArray();
    }
}

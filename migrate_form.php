<?php
$dashboard = file_get_contents('resources/views/components/admin/⚡dashboard.blade.php');
$editForm = file_get_contents('resources/views/components/seller/products/⚡edit.blade.php');

preg_match('/<form wire:submit="save" class="space-y-8">.*?<\/form>/s', $editForm, $matches);
$formHtml = $matches[0];
$formHtml = str_replace('wire:submit="save"', 'wire:submit.prevent="saveProduct"', $formHtml);
$formHtml = str_replace('$categories', '$this->editingCategories', $formHtml);
$formHtml = str_replace('<a href="/seller/products" class=', '<button type="button" wire:click="cancelEdit" class=', $formHtml);
$formHtml = str_replace('</a>', '</button>', $formHtml);

$search = '<div class="bg-[#161615] rounded-[2rem] border border-white/10 p-8 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <h2 class="text-xl font-bold text-[#e8e4df] font-accent">Cross-Store Catalog</h2>';

$replace = '@if($this->editingProductId)
            <div class="border-b border-white/10 pb-8 mb-8">
                <h1 class="text-3xl font-bold text-[#e8e4df]">Edit Product</h1>
            </div>
            ' . $formHtml . '
        @else
        ' . $search;

$dashboard = str_replace($search, $replace, $dashboard);

// also inject @endif at the end of the products tab block
$dashboard = str_replace('</table>
            </div>
        </div>
    @endif
</div>', '</table>
            </div>
        </div>
        @endif
    @endif
</div>', $dashboard);

file_put_contents('resources/views/components/admin/⚡dashboard.blade.php', $dashboard);
echo "Done";

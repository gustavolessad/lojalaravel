<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SearchBar extends Component
{
    public string $query = '';

    #[Computed]
    public function results(): Collection
    {
        if (mb_strlen(trim($this->query)) < 2) {
            return collect();
        }

        return Product::where('active', true)
            ->where('name', 'like', '%' . trim($this->query) . '%')
            ->with('media')
            ->limit(6)
            ->get()
            ->map(fn ($product) => [
                'name'  => $product->name,
                'url'   => route('product.show', ['slug' => $product->slug]),
                'image' => $product->getFirstMediaUrl('cover', 'thumb')
                        ?: $product->getFirstMediaUrl('cover'),
                'price' => $product->getCurrentPrice(),
            ]);
    }

    public function search(): mixed
    {
        $q = trim($this->query);

        if ($q === '') {
            return null;
        }

        return redirect()->to('/busca?q=' . urlencode($q));
    }

    public function render()
    {
        return view('livewire.shop.search-bar');
    }
}

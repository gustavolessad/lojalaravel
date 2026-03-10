<?php

namespace App\Livewire\Shop;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductReview;
use App\Models\Sales\OrderItem;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProductReviews extends Component
{
    use WithPagination, WithFileUploads;

    public int $productId;

    // Form fields
    public int $formRating = 0;
    public string $formTitle = '';
    public string $formComment = '';
    public array $formPhotos = [];

    public bool $showForm = false;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    #[Computed]
    public function product(): Product
    {
        return Product::findOrFail($this->productId);
    }

    #[Computed]
    public function canReview(): bool
    {
        $customer = auth('customer')->user();
        if (! $customer) {
            return false;
        }

        if ($this->existingReview) {
            return false;
        }

        return $this->eligibleOrderItems()->isNotEmpty();
    }

    #[Computed]
    public function existingReview(): ?ProductReview
    {
        $customer = auth('customer')->user();
        if (! $customer) {
            return null;
        }

        return ProductReview::where('product_id', $this->productId)
            ->where('customer_id', $customer->id)
            ->first();
    }

    #[Computed]
    public function ratingDistribution(): array
    {
        $counts = ProductReview::where('product_id', $this->productId)
            ->approved()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = $counts[$i] ?? 0;
        }

        return $distribution;
    }

    private function eligibleOrderItems()
    {
        $customer = auth('customer')->user();

        return OrderItem::where('product_id', $this->productId)
            ->whereHas('order', fn ($q) => $q
                ->where('customer_id', $customer->id)
                ->where('status', 'delivered')
            )
            ->limit(1)
            ->get();
    }

    public function openForm(): void
    {
        $this->showForm = true;
        $this->resetForm();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->formRating  = 0;
        $this->formTitle   = '';
        $this->formComment = '';
        $this->formPhotos  = [];
        $this->resetErrorBag();
    }

    public function removePhoto(int $index): void
    {
        $photos = $this->formPhotos;
        unset($photos[$index]);
        $this->formPhotos = array_values($photos);
    }

    public function submitReview(): void
    {
        $this->validate([
            'formRating'   => ['required', 'integer', 'min:1', 'max:5'],
            'formComment'  => ['required', 'string', 'min:10', 'max:2000'],
            'formTitle'    => ['nullable', 'string', 'max:120'],
            'formPhotos'   => ['array', 'max:5'],
            'formPhotos.*' => ['image', 'max:5120'],
        ], [
            'formRating.required'  => 'Selecione uma nota de 1 a 5.',
            'formRating.min'       => 'Selecione uma nota de 1 a 5.',
            'formComment.required' => 'Escreva seu comentário sobre o produto.',
            'formComment.min'      => 'O comentário deve ter pelo menos 10 caracteres.',
            'formPhotos.max'       => 'Máximo de 5 fotos permitidas.',
            'formPhotos.*.max'     => 'Cada foto pode ter no máximo 5MB.',
        ]);

        $customer  = auth('customer')->user();
        $orderItem = $this->eligibleOrderItems()->first();

        $review = ProductReview::create([
            'product_id'    => $this->productId,
            'customer_id'   => $customer->id,
            'order_item_id' => $orderItem?->id,
            'rating'        => $this->formRating,
            'title'         => $this->formTitle ?: null,
            'comment'       => $this->formComment,
            'status'        => 'pending',
        ]);

        foreach ($this->formPhotos as $photo) {
            $review->addMedia($photo->getRealPath())
                ->usingFileName($photo->hashName())
                ->toMediaCollection('photos');
        }

        $this->showForm = false;
        $this->resetForm();

        session()->flash('review_success', 'Avaliação enviada com sucesso! Ela será publicada após moderação.');
    }

    public function render(): View
    {
        $reviews = ProductReview::where('product_id', $this->productId)
            ->approved()
            ->with(['customer', 'media'])
            ->latest()
            ->paginate(10);

        return view('livewire.shop.product-reviews', compact('reviews'));
    }
}

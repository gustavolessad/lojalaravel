<?php

namespace App\Livewire\Account;

use App\Models\Catalog\ProductReview;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyReviews extends Component
{
    public ?int $editingId = null;
    public int $editRating = 0;
    public string $editTitle = '';
    public string $editComment = '';

    public function startEdit(int $reviewId): void
    {
        $review = $this->getReview($reviewId);
        if (! $review || $review->status !== 'pending') {
            return;
        }

        $this->editingId  = $reviewId;
        $this->editRating  = $review->rating;
        $this->editTitle   = $review->title ?? '';
        $this->editComment = $review->comment;
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function updateReview(): void
    {
        $this->validate([
            'editRating'  => ['required', 'integer', 'min:1', 'max:5'],
            'editComment' => ['required', 'string', 'min:10', 'max:2000'],
            'editTitle'   => ['nullable', 'string', 'max:120'],
        ], [
            'editRating.required'  => 'Selecione uma nota de 1 a 5.',
            'editRating.min'       => 'Selecione uma nota de 1 a 5.',
            'editComment.required' => 'Escreva seu comentário.',
            'editComment.min'      => 'O comentário deve ter pelo menos 10 caracteres.',
        ]);

        $review = $this->getReview($this->editingId);
        if (! $review || $review->status !== 'pending') {
            return;
        }

        $review->update([
            'rating'  => $this->editRating,
            'title'   => $this->editTitle ?: null,
            'comment' => $this->editComment,
        ]);

        $this->editingId = null;
        session()->flash('review_success', 'Avaliação atualizada com sucesso.');
    }

    public function deleteReview(int $reviewId): void
    {
        $review = $this->getReview($reviewId);
        if (! $review) {
            return;
        }

        $product = $review->product;
        $review->delete();
        $product->recalculateRating();

        session()->flash('review_success', 'Avaliação removida com sucesso.');
    }

    private function getReview(int $id): ?ProductReview
    {
        return ProductReview::where('id', $id)
            ->where('customer_id', auth('customer')->id())
            ->first();
    }

    public function render(): View
    {
        $reviews = ProductReview::where('customer_id', auth('customer')->id())
            ->with(['product.media', 'media'])
            ->latest()
            ->get();

        return view('livewire.account.my-reviews', compact('reviews'));
    }
}

<?php

namespace App\Livewire\Shop;

use App\Models\Marketing\NewsletterLead;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NewsletterForm extends Component
{
    public string $name  = '';
    public string $email = '';
    public string $phone = '';

    public bool $subscribed = false;

    public function subscribe(): void
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        NewsletterLead::firstOrCreate(
            ['email' => strtolower(trim($this->email))],
            [
                'name'  => trim($this->name),
                'phone' => trim($this->phone) ?: null,
            ],
        );

        $this->subscribed = true;
        $this->reset('name', 'email', 'phone');
    }

    public function render(): View
    {
        return view('livewire.shop.newsletter-form');
    }
}

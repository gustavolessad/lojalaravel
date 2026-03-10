<?php

namespace App\Models\Customer;

use App\Models\Catalog\ProductReview;
use App\Models\Sales\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, InteractsWithMedia;

    protected $fillable = [
        'type',
        'name',
        'cpf',
        'rg',
        'birth_date',
        'company_name',
        'cnpj',
        'state_registration',
        'responsible_name',
        'email',
        'password',
        'phone',
        'mobile',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'password'   => 'hashed',
        ];
    }

    public function isPF(): bool
    {
        return $this->type === 'pf';
    }

    public function isPJ(): bool
    {
        return $this->type === 'pj';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->isPJ() ? ($this->company_name ?? $this->name) : $this->name;
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function defaultAddress(): HasMany
    {
        return $this->hasMany(CustomerAddress::class)->where('is_default', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }
}

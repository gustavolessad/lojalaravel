<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = [
        'from_url',
        'to_url',
        'status_code',
        'active',
        'hits',
        'last_hit_at',
    ];

    protected function casts(): array
    {
        return [
            'active'      => 'boolean',
            'last_hit_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;

class NewsletterLead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];
}

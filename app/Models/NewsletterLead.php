<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterLead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiIntegration extends Model
{
    protected $fillable = [
        'category',
        'service_name',
        'label',
        'api_key',
        'api_secret',
        'api_url',
        'extra_data',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

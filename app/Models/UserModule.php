<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserModule extends Pivot
{
    use HasFactory;

    protected $table = 'user_modules';

    protected $fillable = [
        'user_id',
        'module_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];
}

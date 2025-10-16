<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'price',
        'stock',
        'min_stock'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function hasSufficientStock($quantity)
    {
        return $this->stock >= $quantity;
    }

    public function isLowStock()
    {
        return $this->stock <= $this->min_stock;
    }

    public function decrementStock($quantity)
    {
        $this->decrement('stock', $quantity);
        $this->refresh();
    }

    public function incrementStock($quantity)
    {
        $this->increment('stock', $quantity);
        $this->refresh();
    }
}

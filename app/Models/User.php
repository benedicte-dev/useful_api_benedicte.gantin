<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'user_modules')
            ->withPivot('active')
            ->withTimestamps();
    }

    public function isModuleActive($moduleId)
    {
        return $this->modules()
            ->where('module_id', $moduleId)
            ->where('active', true)
            ->exists();
    }

    public function shortLinks()
    {
        return $this->hasMany(ShortLink::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'sender_id');
    }

    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'receiver_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function boughtOrders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function soldOrders()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }
}

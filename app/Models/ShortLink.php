<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShortLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_url',
        'code',
        'clicks'
    ];

    protected $casts = [
        'clicks' => 'integer'
    ];

    /**
     * Génère un code unique
     */
    public static function generateUniqueCode($length = 6)
    {
        do {
            $code = Str::random($length);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Incrémente le compteur de clics
     */
    public function incrementClicks()
    {
        $this->increment('clicks');
    }
}

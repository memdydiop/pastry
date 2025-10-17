<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserProfile extends Model
{
    protected $table = "user_profiles";

    protected $fillable = [
        'full_name',
        'date_of_birth',
        'phone',
        'address',
        'city',
        'country',
        'bio',
        'avatar',
    ];
    
    /**
    * The attributes that should be cast.
    *
    * @var array
    */
    protected $casts = [
       'date_of_birth' => 'date',
    ];

    /**
     * Obtenir l'utilisateur auquel le profil appartient.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcule les initiales de l'utilisateur à partir du nom complet.
     */
    public function initials(): string
    {
        if (empty($this->full_name)) {
            return '';
        }

        return Str::of($this->full_name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Accesseur pour obtenir l'URL complète de l'avatar.
     */
    public function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            // Si un avatar est défini, retourne son URL via le disque 'public'.
            // Sinon, retourne une URL par défaut depuis ui-avatars.com.
            return $this->avatar
                ? Storage::url(auth()->user()->profile->avatar)
                : 'https://ui-avatars.com/api/?name=' . urlencode($this->initials() ?: 'P') . '&background=random';
        });
    }
}
//Storage::url(auth()->user()->profile->photo)

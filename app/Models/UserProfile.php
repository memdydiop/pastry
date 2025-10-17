<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

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
       'date_of_birth' => 'date', // Ajouté pour caster la date
   ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
{
    // Supprimer l'ancien avatar lors d'une mise à jour
    static::updating(function ($profile) {
        if ($profile->isDirty('avatar') && $profile->getOriginal('avatar')) {
            Storage::disk('public')->delete($profile->getOriginal('avatar'));
        }
    });
    
    // Supprimer l'avatar lors de la suppression du profil
    static::deleting(function ($profile) {
        if ($profile->avatar) {
            Storage::disk('public')->delete($profile->avatar);
        }
    });
}

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->full_name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }/**
     * Accesseur pour obtenir l'URL complète de l'avatar.
     */
    public function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            // Si un avatar est défini, retourne son URL via le disque 'public'.
            // Sinon, retourne une URL par défaut (par exemple, depuis ui-avatars.com).
            return $this->avatar
                ? Storage::disk('public')->url($this->avatar)
                : 'https://ui-avatars.com/api/?name=' . urlencode($this->initials()) . '&background=random';
        });
    }
}

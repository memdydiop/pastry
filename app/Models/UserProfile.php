<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Models\User;
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
    }
}

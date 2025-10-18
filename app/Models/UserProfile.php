<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserProfile extends Model
{/**
     * 💡 Touches le modèle parent `User` chaque fois que ce modèle est mis à jour.
     *
     * @var array
     */
    protected $touches = ['user'];

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
            ->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');
    }

    /**
     * Accesseur pour obtenir l'URL complète de l'avatar.
     * Le résultat est mis en cache pour améliorer les performances.
     * 
     * Note: Le nom de la méthode doit correspondre à l'attribut sans "Attribute"
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Clé de cache unique pour l'avatar de cet utilisateur.
                $cacheKey = 'user:' . $this->user_id . ':avatar';

                // On garde le résultat en cache pendant 1 heure (3600 secondes).
                return Cache::remember($cacheKey, 3600, function () use ($value) {
                    // Si un avatar est défini, retourne son URL via le disque 'public'.
                    if ($value) {
                        return Storage::disk('public')->url($value);
                    }
                    
                    // Sinon, retourne une URL par défaut depuis ui-avatars.com.
                    $initials = $this->initials() ?: 'U';
                    $bgColor = hash('crc32b', (string) $this->user_id);
                    return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=random';
                });
            }
        );
    }

    /**
     * Vide le cache de l'avatar pour cet utilisateur.
     */
    public function clearAvatarCache(): void
    {
        Cache::forget('user:' . $this->user_id . ':avatar');
        
        // 💡 Suppression du fichier physique de l'avatar sur le disque de stockage
        if ($this->attributes['avatar']) {
            Storage::disk('public')->delete($this->attributes['avatar']);
        }
    }

    /**
     * Obtient le chemin du fichier avatar (sans l'URL complète)
     */
    public function getAvatarPathAttribute(): ?string
    {
        return $this->attributes['avatar'] ?? null;
    }
}
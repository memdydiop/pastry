<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $fillable = [
        'email',
        'password',
        'profile_completed',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected $appends = [
        'is_admin',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'profile_completed' => 'boolean',
        ];
    }
    
    /**
     * Accesseur pour vérifier si l'utilisateur est un administrateur.
     */
    protected function getIsAdminAttribute(): bool
    {
        return Cache::remember(
            "user:{$this->id}:is_admin",
            now()->addHour(),
            fn() => $this->hasAnyRole(['admin', 'Ghost'])
        );
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile?->full_name ?? $this->email,
        );
    }

    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile?->avatar ?? '',
        );
    }

    public function initials(): string
    {
        if ($this->profile?->full_name) {
            return $this->profile->initials();
        }

        $prefix = Str::before($this->email, '@');
        return Str::upper(Str::substr($prefix, 0, 2) ?: 'U');
    }

    public function clearAvatarCache(): void
    {
        $this->profile?->clearAvatarCache();
    }

    protected static function boot()
    {
        parent::boot();

        // Invalider le cache is_admin lors de la sauvegarde
        static::saved(function (User $user) {
            Cache::forget("user:{$user->id}:is_admin");
        });

        // Eager load automatiquement le profil
        static::retrieved(function ($user) {
            if (! $user->relationLoaded('profile')) {
                $user->load('profile');
            }
        });
    }
}
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\UserProfile;
use Spatie\Permission\Traits\HasRoles;

/**
 * The attributes that are mass assignable.
 *
 * @var list<string>
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'profile_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
        // Vérifie si l'utilisateur possède le rôle 'admin' ou 'Ghost'
        return $this->hasAnyRole(['admin', 'Ghost']);
    }

    /**
     * Obtenir le profil associé à l'utilisateur.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Accesseur pour obtenir le nom à afficher.
     * 
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile?->full_name ?? $this->email,
        );
    }

    /**
     * Accesseur pour obtenir l'URL de la photo de profil.
     * 
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile?->avatar ?? '',
        );
    }

    /**
     * Délégué pour obtenir les initiales.
     * C'est le point d'accès unique pour toute l'application.
     */
    public function initials(): string
    {
        // Si le profil existe, on lui demande de calculer les initiales.
        if ($this->profile?->full_name) {
            return $this->profile->initials();
        }

        // Solution de repli : calculer les initiales à partir de l'email
        //return Str::upper(Str::substr($this->email, 0, 2));
        $prefix = Str::before($this->email, '@');
        return Str::upper(Str::substr($prefix, 0, 2) ?: 'U');
    }

    /**
     * Vider le cache de l'avatar de l'utilisateur.
     * 
     * @return void
     */
    public function clearAvatarCache(): void
    {
        $this->profile?->clearAvatarCache();
    }

    /**
     * Boot method pour enregistrer les événements du modèle.
     */
    protected static function boot()
    {
        parent::boot();

        // Eager load automatiquement le profil pour éviter les N+1 queries
        static::retrieved(function ($user) {
            if (! $user->relationLoaded('profile')) {
                $user->load('profile');
            }
        });
    }
}

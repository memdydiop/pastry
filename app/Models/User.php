<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\UserProfile;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
     * Obtenir le profil associé à l'utilisateur.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Délégué pour obtenir le nom à afficher.
     */
    public function getNameAttribute(): string
    {
        return $this->profile?->full_name ?? $this->email;
    }

    /**
     * Délégué pour obtenir l'URL de la photo de profil.
     */
    public function getAvatarAttribute(): string
    {
        return $this->profile?->avatar_url ?? '';
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

        // Sinon, solution de repli : on calcule les initiales à partir de l'email.
        return Str::of($this->email)
            ->explode('@')
            ->first()
            ->substr(0, 2)
            ->upper();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserProfile extends Model
{
    /**
     * Champs de profil requis pour la complétion.
     */
    public const REQUIRED_FIELDS = [
        'full_name',
        'date_of_birth',
        'phone',
        'address',
        'city',
        'country',
        'bio',
    ];
    
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
    
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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

    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Cache::remember(
                "user:{$this->user_id}:avatar", 
                3600, 
                function () use ($value) {
                    if ($value && Storage::disk('public')->exists($value)) {
                        return Storage::disk('public')->url($value);
                    }
                
                    $initials = $this->initials() ?: 'U';
                    $bgColor = substr(hash('crc32b', (string) $this->user_id), 0, 6);
                    
                    return sprintf(
                        'https://ui-avatars.com/api/?name=%s&background=%s&color=fff&size=200',
                        urlencode($initials),
                        $bgColor
                    );
                }
            )
        );
    }

    public function clearAvatarCache(): void
    {
        Cache::forget("user:{$this->user_id}:avatar");
    }

    public function deleteAvatarFile(?string $path = null): bool
    {
        $avatarPath = $path ?? $this->getRawOriginal('avatar');
        
        if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
            return Storage::disk('public')->delete($avatarPath);
        }
        
        return false;
    }

    public function getAvatarPathAttribute(): ?string
    {
        return $this->getRawOriginal('avatar');
    }
}
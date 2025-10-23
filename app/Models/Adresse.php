<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adresse extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'type',
        'adresse',
        'complement_adresse',
        'code_postal',
        'ville',
        'pays',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Accessors
    public function getAdresseCompleteAttribute(): string
    {
        $parts = array_filter([
            $this->adresse,
            $this->complement_adresse,
            $this->code_postal . ' ' . $this->ville,
            $this->pays !== 'Côte d\'Ivoire' ? $this->pays : null,
        ]);

        return implode(', ', $parts);
    }

    public function getAdresseCourtAttribute(): string
    {
        return $this->adresse . ', ' . $this->ville;
    }

    // Methods
    public function definirParDefaut(): void
    {
        // Retirer le défaut des autres adresses du même client
        $this->client->adresses()
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }
}
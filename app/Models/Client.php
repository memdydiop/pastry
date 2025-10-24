<?php

namespace App\Models;

use App\Enums\TypeClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;//, LogsActivity;

    protected $fillable = [
        'type',
        'nom',
        'raison_sociale',
        'email',
        'telephone',
        'telephone_secondaire',
        'points_fidelite',
        'score_client',
        'notes',
    ];

    protected $casts = [
        'type' => TypeClient::class,
        'preferences_alimentaires' => 'array',
        'produits_favoris' => 'array',
        'points_fidelite' => 'integer',
        'score_client' => 'decimal:2',
    ];

    // Relations
    public function adresses(): HasMany
    {
        return $this->hasMany(Adresse::class);
    }
    
    // NOUVEAU: Ajout du placeholder pour la relation Commandes
    //public function commandes(): HasMany
    //{
        // Supposons que le modèle Commande existe
    //    return $this->hasMany(\App\Models\Commande::class);
    //}

    // Accessors
    public function getNomCompletAttribute(): string
    {
        if ($this->type === TypeClient::ENTREPRISE) {
            return $this->raison_sociale ?? '';
        }
        return $this->nom ?? '';
    }

    public function getAdresseDefaultAttribute(): ?Adresse
    {
        return $this->adresses()->where('is_default', true)->first() 
            ?? $this->adresses()->first();
    }

    public function getInitialesAttribute(): string
    {
        if ($this->type === TypeClient::ENTREPRISE) {
            $words = explode(' ', $this->raison_sociale ?? '');
            return strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1));
        }
        
        return strtoupper(substr($this->nom, 0, 1));
    }

    // Scopes
    public function scopeParticuliers($query)
    {
        return $query->where('type', TypeClient::PARTICULIER);
    }

    public function scopeEntreprises($query)
    {
        return $query->where('type', TypeClient::ENTREPRISE);
    }

    public function scopeVip($query)
    {
        return $query->where('score_client', '>=', 80);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nom', 'like', "%{$search}%")
              ->orWhere('raison_sociale', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('telephone', 'like', "%{$search}%")
              ->orWhere('telephone_secondaire', 'like', "%{$search}%");;
        });
    }

    // Methods
    public function ajouterPoints(int $points): void
    {
        $this->increment('points_fidelite', $points);
    }

    public function calculerScore(): float
    {
        //$nombreCommandes = $this->commandes()->count();
        //$montantTotal = $this->commandes()->sum('montant_total');
        $nombreCommandes = 0; // À implémenter quand module commandes sera prêt
        $montantTotal = 0;
        $anciennete = $this->created_at->diffInMonths(now());

        // Formule de scoring (personnalisable)
        $score = ($nombreCommandes * 10) + ($montantTotal / 100) + ($anciennete * 2);
        
        $scoreCalcule = min($score, 100);
        $this->update(['score_client' => $scoreCalcule]);
        
        return $scoreCalcule;
    }

    public function estVip(): bool
    {
        return $this->score_client >= 80;
    }
}
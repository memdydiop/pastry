<?php

namespace App\Enums;

enum StatutCommande: string
{
    case BROUILLON = 'brouillon';
    case EN_ATTENTE_VALIDATION = 'en_attente_validation';
    case CONFIRMEE = 'confirmee';
    case EN_PRODUCTION = 'en_production';
    case PRETE = 'prete';
    case EN_LIVRAISON = 'en_livraison';
    case LIVREE = 'livree';
    case ANNULEE = 'annulee';
    case REMBOURSEE = 'remboursee';

    public function label(): string
    {
        return match($this) {
            self::BROUILLON => 'Brouillon',
            self::EN_ATTENTE_VALIDATION => 'En attente',
            self::CONFIRMEE => 'Confirmée',
            self::EN_PRODUCTION => 'En production',
            self::PRETE => 'Prête',
            self::EN_LIVRAISON => 'En livraison',
            self::LIVREE => 'Livrée',
            self::ANNULEE => 'Annulée',
            self::REMBOURSEE => 'Remboursée',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::BROUILLON => 'gray',
            self::EN_ATTENTE_VALIDATION => 'yellow',
            self::CONFIRMEE => 'blue',
            self::EN_PRODUCTION => 'purple',
            self::PRETE => 'green',
            self::EN_LIVRAISON => 'indigo',
            self::LIVREE => 'emerald',
            self::ANNULEE => 'red',
            self::REMBOURSEE => 'orange',
        };
    }
}
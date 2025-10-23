<?php

namespace App\Enums;

enum TypeClient: string
{
    case PARTICULIER = 'particulier';
    case ENTREPRISE = 'entreprise';

    public function label(): string
    {
        return match($this) {
            self::PARTICULIER => 'Particulier',
            self::ENTREPRISE => 'Entreprise',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::PARTICULIER => 'zinc',
            self::ENTREPRISE => 'blue',
        };
    }

    public static function toArray(): array
    {
        return [
            self::PARTICULIER->value => self::PARTICULIER->label(),
            self::ENTREPRISE->value => self::ENTREPRISE->label(),
        ];
    }
}
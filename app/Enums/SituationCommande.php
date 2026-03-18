<?php

namespace App\Enums;

enum SituationCommande: string
{
    case PAYEE = 'Payé';
    case EN_COMPTE = 'En compte';
    case CREDIT = 'Crédit';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

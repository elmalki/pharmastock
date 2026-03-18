<?php

namespace App\Enums;

enum StatutVente: string
{
    case PAYEE = 'payé';
    case PARTIEL = 'partiel';
    case ANNULEE = 'annulé';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

<?php

namespace App\Enums;

enum ModePaiement: string
{
    case ESPECE = 'Éspèce';
    case CHEQUE = 'Chèque';
    case EFFET = 'Effet';
    case VIREMENT = 'Virement';
    case TPE = 'TPE';
    case EN_COMPTE = 'En compte';
    case CREDIT = 'Crédit';
    case MULTI = 'Multi Réglement';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

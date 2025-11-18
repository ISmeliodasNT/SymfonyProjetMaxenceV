<?php

namespace App\Enum;

enum Status: string {
    case DISPONIBLE = 'Disponible';
    case RUPTURE_DE_STOCK = 'Rupture_de_stock';
    case PRECOMMANDE = 'Precommande';
}

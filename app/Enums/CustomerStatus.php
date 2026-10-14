<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CustomerStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Trial = 'trial';
    case Churned = 'churned';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Trial => 'warning',
            self::Churned => 'danger',
        };
    }
}

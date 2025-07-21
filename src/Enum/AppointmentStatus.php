<?php

namespace App\Enum;

enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CONFIRMED = 'confirmed';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => "En attente",
            self::PAID => "RDV Payé",
            self::CONFIRMED => "RDV confirmé",
            self::CANCELED => "RDV annulé",
        };
    }
}

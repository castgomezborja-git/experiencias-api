<?php

declare(strict_types=1);

namespace App\Reservation\Domain\Model\ValueObject;

enum ReservationStatus: string
{
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
}
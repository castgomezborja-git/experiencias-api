<?php

declare(strict_types=1);

namespace App\Reservation\Application\DTO;

final class CancelReservationCommand
{
    public function __construct(
        public readonly string $reservationId
    ) {
    }
}
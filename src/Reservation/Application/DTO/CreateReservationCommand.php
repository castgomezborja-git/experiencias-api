<?php

declare(strict_types=1);

namespace App\Reservation\Application\DTO;

final class CreateReservationCommand
{
    public function __construct(
        public readonly string $sessionId,
        public readonly string $userId,
        public readonly int $seats
    ) {
    }
}
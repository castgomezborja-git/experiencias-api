<?php

declare(strict_types=1);

namespace App\Reservation\Domain\Repository;

use App\Reservation\Domain\Model\Reservation;
use App\Reservation\Domain\Model\ValueObject\ReservationId;
use App\Experience\Domain\Model\ValueObject\SessionId;

interface ReservationRepository
{
    public function save(Reservation $reservation): void;

    public function findById(ReservationId $id): ?Reservation;

    public function totalReservedSeatsForSession(SessionId $sessionId): int;
}
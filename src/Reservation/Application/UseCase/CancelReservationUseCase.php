<?php

declare(strict_types=1);

namespace App\Reservation\Application\UseCase;

use App\Experience\Domain\Repository\SessionRepository;
use App\Reservation\Application\DTO\CancelReservationCommand;
use App\Reservation\Domain\Exception\CancellationWindowExpiredException;
use App\Reservation\Domain\Model\Reservation;
use App\Reservation\Domain\Model\ValueObject\ReservationId;
use App\Reservation\Domain\Repository\ReservationRepository;

final class CancelReservationUseCase
{
    public function __construct(
        private readonly ReservationRepository $reservationRepository,
        private readonly SessionRepository $sessionRepository
    ) {
    }

    public function execute(CancelReservationCommand $command): Reservation
    {
        $reservationId = ReservationId::fromString($command->reservationId);
        $reservation = $this->reservationRepository->findById($reservationId);

        if ($reservation === null) {
            throw new \InvalidArgumentException('Reservation not found');
        }

        $session = $this->sessionRepository->findById($reservation->getSessionId());

        if ($session === null) {
            throw new \InvalidArgumentException('Session not found.');
        }

        if (!$session->canBeCancelledAt(new \DateTimeImmutable())) {
            throw new CancellationWindowExpiredException(
                'Cannot cancel a reservation less than 24 hours before the session starts'
            );
        }

        $reservation->cancel();

        $this->reservationRepository->save($reservation);

        return $reservation;
    }
}
<?php

declare(strict_types=1);

namespace App\Reservation\Application\UseCase;

use App\Reservation\Domain\Model\Reservation;
use App\Reservation\Domain\Model\ValueObject\ReservationId;
use App\Reservation\Domain\Model\ValueObject\UserId;
use App\Reservation\Domain\Repository\ReservationRepository;
use App\Experience\Domain\Model\ValueObject\SessionId;
use App\Experience\Domain\Repository\SessionRepository;
use App\Reservation\Application\DTO\CreateReservationCommand;
use App\Reservation\Domain\Exception\SessionFullyBookedException;
use App\Reservation\Domain\Exception\SessionAlreadyStartedException;
use App\Reservation\Domain\Notification\NotificationSender;
use Doctrine\ORM\EntityManagerInterface;

final class CreateReservationUseCase
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
        private readonly ReservationRepository $reservationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificationSender $notificationSender,
    ) {
    }

    public function execute(CreateReservationCommand $command): Reservation
    {
        $sessionId = SessionId::fromString($command->sessionId);

        $this->entityManager->beginTransaction();

        try {
            $session = $this->sessionRepository->byIdWithLock($sessionId);

            if ($session === null) {
                throw new \InvalidArgumentException('Session not found');
            }

            if ($session->hasStarted()) {
                throw new SessionAlreadyStartedException('Cannot reserve a session that has already started');
            }

            $alreadyReserved = $this->reservationRepository->totalReservedSeatsForSession($sessionId);

            if ($alreadyReserved + $command->seats > $session->getMaxCapacity()) {
                throw new SessionFullyBookedException('Not enough seats available for this session');
            }

            $reservation = Reservation::create(
                id: ReservationId::generate(),
                sessionId: $sessionId,
                userId: UserId::fromString($command->userId),
                seats: $command->seats,
                totalPrice: $command->seats * $session->getPrice(),
            );

            $this->reservationRepository->save($reservation);

            $this->notificationSender->sendReservationConfirmed($command->userId, $reservation->getId()->getValue());

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            throw $e;
        }

        return $reservation;
    }
}
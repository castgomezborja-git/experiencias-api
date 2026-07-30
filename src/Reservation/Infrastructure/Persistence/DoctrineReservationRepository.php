<?php

declare(strict_types=1);

namespace App\Reservation\Infrastructure\Persistence;

use App\Experience\Domain\Model\ValueObject\SessionId;
use App\Reservation\Domain\Model\ValueObject\ReservationId;
use App\Reservation\Domain\Model\ValueObject\ReservationStatus;
use App\Reservation\Domain\Repository\ReservationRepository;
use App\Reservation\Domain\Model\Reservation;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineReservationRepository implements ReservationRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(Reservation $reservation): void
    {
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();
    }

    public function findById(ReservationId $id): ?Reservation
    {
        return $this->entityManager->find(Reservation::class, $id);
    }

    public function totalReservedSeatsForSession(SessionId $sessionId): int
    {
        $result = $this->entityManager->createQueryBuilder()
        ->select('COALESCE(SUM(r.seats), 0)')
        ->from(Reservation::class, 'r')
        ->where('r.sessionId = :sessionId')
        ->andWhere('r.status = :status')
        ->setParameter('sessionId', $sessionId->getValue())
        ->setParameter('status', ReservationStatus::CONFIRMED->value)
        ->getQuery()
        ->getSingleScalarResult();

        return (int) $result;
    }
}
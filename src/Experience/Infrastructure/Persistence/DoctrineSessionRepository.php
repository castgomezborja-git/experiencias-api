<?php

declare(strict_types=1);

namespace App\Experience\Infrastructure\Persistence;

use App\Experience\Domain\Model\Session;
use App\Experience\Domain\Model\ValueObject\SessionId;
use App\Experience\Domain\Model\ValueObject\ExperienceId;
use App\Experience\Domain\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\LockMode;

final class DoctrineSessionRepository implements SessionRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(Session $session): void
    {
        $this->entityManager->persist($session);
        $this->entityManager->flush();
    }

    public function findById(SessionId $id): ?Session
    {
        return $this->entityManager->find(Session::class, $id);
    }

    public function existsForExperienceOnDate(ExperienceId $experienceId, \DateTimeImmutable $date): bool
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        $count = $this->entityManager->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from(Session::class, 's')
            ->where('s.experienceId = :experienceId')
            ->andWhere('s.date BETWEEN :startOfDay AND :endOfDay')
            ->setParameter('experienceId', $experienceId->getValue())
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function byIdWithLock(SessionId $id): ?Session
    {
        return $this->entityManager->find(
            Session::class,
            $id,
            LockMode::PESSIMISTIC_WRITE,
        );
    }

    public function byId(SessionId $id): ?Session
    {
        return $this->entityManager->find(Session::class, $id);
    }
}
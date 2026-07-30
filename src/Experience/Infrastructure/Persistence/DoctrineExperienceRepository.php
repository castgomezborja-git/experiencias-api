<?php

declare(strict_types=1);

namespace App\Experience\Infrastructure\Persistence;

use App\Experience\Domain\Model\Experience;
use App\Experience\Domain\Model\ValueObject\ExperienceId;
use App\Experience\Domain\Repository\ExperienceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineExperienceRepository implements ExperienceRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(Experience $experience): void
    {
        $this->entityManager->persist($experience);
        $this->entityManager->flush();
    }

    public function findById(ExperienceId $id): ?Experience
    {
        return $this->entityManager->find(Experience::class, $id->getValue());
    }
}
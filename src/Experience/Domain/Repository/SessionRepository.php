<?php

declare(strict_types=1);

namespace App\Experience\Domain\Repository;

use App\Experience\Domain\Model\Session;
use App\Experience\Domain\Model\ValueObject\SessionId;
use App\Experience\Domain\Model\ValueObject\ExperienceId;

interface SessionRepository
{
    public function save(Session $session): void;

    public function findById(SessionId $id): ?Session;

    public function existsForExperienceOnDate(ExperienceId $experienceId, \DateTimeImmutable $date): bool;
}
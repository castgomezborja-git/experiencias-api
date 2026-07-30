<?php

declare(strict_types=1);

namespace App\Experience\Domain\Repository;

use App\Experience\Domain\Model\Experience;
use App\Experience\Domain\Model\ValueObject\ExperienceId;

interface ExperienceRepository
{
    public function save(Experience $experience): void;

    public function findById(ExperienceId $id): ?Experience;
}
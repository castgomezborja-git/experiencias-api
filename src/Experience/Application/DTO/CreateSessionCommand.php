<?php

declare(strict_types=1);

namespace App\Experience\Application\DTO;

final class CreateSessionCommand
{
    public function __construct(
        public readonly string $experienceId,
        public readonly string $date,
        public readonly int $maxCapacity,
        public readonly int $price
    ) {
    }
}
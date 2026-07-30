<?php

declare(strict_types=1);

namespace App\Experience\Application\DTO;

final class RegisterExperienceCommand
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $providerId
    ) {
    }
}
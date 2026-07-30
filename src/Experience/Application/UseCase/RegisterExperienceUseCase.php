<?php

declare(strict_types=1);

namespace App\Experience\Application\UseCase;

use App\Experience\Application\DTO\RegisterExperienceCommand;
use App\Experience\Domain\Model\Experience;
use App\Experience\Domain\Model\ValueObject\ExperienceId;
use App\Experience\Domain\Model\ValueObject\ProviderId;
use App\Experience\Domain\Repository\ExperienceRepository;

final class RegisterExperienceUseCase
{
    public function __construct(
        private readonly ExperienceRepository $experienceRepository
    ) {
    }

    public function execute(RegisterExperienceCommand $command): Experience
    {
        $experience = Experience::register(
            ExperienceId::generate(),
            ProviderId::fromString($command->providerId),
            $command->title,
            $command->description,
        );

        $this->experienceRepository->save($experience);

        return $experience;
    }
}
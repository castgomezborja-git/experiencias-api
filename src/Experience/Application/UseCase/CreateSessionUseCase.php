<?php

declare(strict_types=1);

namespace App\Experience\Application\UseCase;

use App\Experience\Application\DTO\CreateSessionCommand;
use App\Experience\Domain\Model\Session;
use App\Experience\Domain\Model\ValueObject\ExperienceId;
use App\Experience\Domain\Model\ValueObject\SessionId;
use App\Experience\Domain\Repository\SessionRepository;
use App\Experience\Domain\Exception\DuplicateSessionDateException;

final class CreateSessionUseCase
{
    public function __construct(
        private readonly SessionRepository $sessionRepository
    ) {
    }

    public function execute(CreateSessionCommand $command): Session
    {
        $experienceId = ExperienceId::fromString($command->experienceId);
        $date = new \DateTimeImmutable($command->date);

        if ($this->sessionRepository->existsForExperienceOnDate($experienceId, $date)) {
            throw new DuplicateSessionDateException(
                'A session already exists for this experience on this date'
            );
        }

        $session = Session::schedule(
            id: SessionId::generate(),
            experienceId: $experienceId,
            date: $date,
            maxCapacity: $command->maxCapacity,
            price: $command->price,
        );

        $this->sessionRepository->save($session);

        return $session;
    }
}
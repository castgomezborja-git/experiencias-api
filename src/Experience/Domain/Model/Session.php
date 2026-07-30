<?php

declare(strict_types=1);

namespace App\Experience\Domain\Model;

use App\Experience\Domain\Model\ValueObject\SessionId;
use App\Experience\Domain\Model\ValueObject\ExperienceId;

final class Session
{
    public function __construct(
        private readonly SessionId $id,
        private readonly ExperienceId $experienceId,
        private readonly \DateTimeImmutable $date,
        private readonly int $maxCapacity,
        private readonly int $price,
    ) {
    }

    public function getId(): SessionId
    {
        return $this->id;
    }

    public function getExperienceId(): ExperienceId
    {
        return $this->experienceId;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getMaxCapacity(): int
    {
        return $this->maxCapacity;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function hasStarted(): bool
    {
        return $this->date <= new \DateTimeImmutable();
    }

    public static function schedule(
        SessionId $id,
        ExperienceId $experienceId,
        \DateTimeImmutable $date,
        int $maxCapacity,
        int $price,
    ): self {
        if ($date < new \DateTimeImmutable('today')) {
            throw new \InvalidArgumentException('Cannot create a session in the past');
        }

        if ($maxCapacity <= 0) {
            throw new \InvalidArgumentException('Max capacity must be greater than zero');
        }

        if ($price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }

        return new self($id, $experienceId, $date, $maxCapacity, $price);
    }
}
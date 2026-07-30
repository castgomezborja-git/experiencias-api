<?php

declare(strict_types=1);

namespace App\Reservation\Domain\Model;

use App\Experience\Domain\Model\ValueObject\SessionId;
use App\Reservation\Domain\Model\ValueObject\ReservationId;
use App\Reservation\Domain\Model\ValueObject\ReservationStatus;
use App\Reservation\Domain\Model\ValueObject\UserId;

final class Reservation
{
    public function __construct(
        private readonly ReservationId $id,
        private readonly SessionId $sessionId,
        private readonly UserId $userId,
        private readonly int $seats,
        private readonly int $totalPrice,
        private ReservationStatus $status,
    ) {
    }

    public static function create(
        ReservationId $id,
        SessionId $sessionId,
        UserId $userId,
        int $seats,
        int $totalPrice,
    ): self {
        if ($seats <= 0) {
            throw new \InvalidArgumentException('Seats must be greater than zero');
        }

        return new self($id, $sessionId, $userId, $seats, $totalPrice, ReservationStatus::CONFIRMED);
    }

    public function cancel(): void
    {
        if ($this->status === ReservationStatus::CANCELLED) {
            throw new \DomainException('Reservation is already canceled');
        }

        $this->status = ReservationStatus::CANCELLED;
    }

    public function isCancelled(): bool
    {
        return $this->status === ReservationStatus::CANCELLED;
    }

    public function getId(): ReservationId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getSessionId(): SessionId
    {
        return $this->sessionId;
    }

    public function getStatus(): ReservationStatus
    {
        return $this->status;
    }

    public function getSeats(): int
    {
        return $this->seats;
    }

    public function getTotalPrice(): int
    {
        return $this->totalPrice;
    }
}
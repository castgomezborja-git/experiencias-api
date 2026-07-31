<?php

declare(strict_types=1);

namespace App\Tests\Reservation\Domain\Model;

use App\Experience\Domain\Model\ValueObject\SessionId;
use App\Reservation\Domain\Exception\ReservationAlreadyCancelledException;
use App\Reservation\Domain\Model\Reservation;
use App\Reservation\Domain\Model\ValueObject\ReservationId;
use App\Reservation\Domain\Model\ValueObject\ReservationStatus;
use App\Reservation\Domain\Model\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class ReservationTest extends TestCase
{
    public function test_creates_reservation_as_confirmed(): void
    {
        $reservation = Reservation::create(
            id: ReservationId::generate(),
            sessionId: SessionId::generate(),
            userId: UserId::fromString('user-1'),
            seats: 3,
            totalPrice: 3000,
        );

        $this->assertSame(ReservationStatus::CONFIRMED, $reservation->getStatus());
    }

    public function test_cannot_create_reservation_with_zero_seats(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Reservation::create(
            id: ReservationId::generate(),
            sessionId: SessionId::generate(),
            userId: UserId::fromString('user-1'),
            seats: 0,
            totalPrice: 0,
        );
    }

    public function test_cancel_changes_status_to_cancelled(): void
    {
        $reservation = Reservation::create(
            id: ReservationId::generate(),
            sessionId: SessionId::generate(),
            userId: UserId::fromString('user-1'),
            seats: 2,
            totalPrice: 2000,
        );

        $reservation->cancel();

        $this->assertTrue($reservation->isCancelled());
    }

    public function test_cannot_cancel_reservation_twice(): void
    {
        $reservation = Reservation::create(
            id: ReservationId::generate(),
            sessionId: SessionId::generate(),
            userId: UserId::fromString('user-1'),
            seats: 2,
            totalPrice: 2000,
        );

        $reservation->cancel();

        $this->expectException(ReservationAlreadyCancelledException::class);

        $reservation->cancel();
    }
}
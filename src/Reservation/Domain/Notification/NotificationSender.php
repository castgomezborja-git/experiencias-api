<?php

declare(strict_types=1);

namespace App\Reservation\Domain\Notification;

interface NotificationSender
{
    public function sendReservationConfirmed(string $email, string $reservationId): void;

    public function sendReservationCancelled(string $email, string $reservationId): void;
}
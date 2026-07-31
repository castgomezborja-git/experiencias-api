<?php

declare(strict_types=1);

namespace App\Reservation\Infrastructure\Notification;

use App\Reservation\Domain\Notification\NotificationSender;
use Psr\Log\LoggerInterface;

final class LoggerNotificationSender implements NotificationSender
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function sendReservationConfirmed(string $email, string $reservationId): void
    {
        $this->logger->info(sprintf(
            '[EMAIL] Sending confirmation email to %s for reservation %s',
            $email,
            $reservationId,
        ));
    }

    public function sendReservationCancelled(string $email, string $reservationId): void
    {
        $this->logger->info(sprintf(
            '[EMAIL] Sending cancellation email to %s for reservation %s',
            $email,
            $reservationId,
        ));
    }
}
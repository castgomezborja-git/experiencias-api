<?php

declare(strict_types=1);

namespace App\Tests\Experience\Domain\Model;

use App\Experience\Domain\Model\Session;
use App\Experience\Domain\Model\ValueObject\ExperienceId;
use App\Experience\Domain\Model\ValueObject\SessionId;
use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    public function test_cannot_schedule_session_in_the_past(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Session::schedule(
            id: SessionId::generate(),
            experienceId: ExperienceId::generate(),
            date: new \DateTimeImmutable('yesterday'),
            maxCapacity: 10,
            price: 1000,
        );
    }

    public function test_cannot_schedule_session_with_zero_capacity(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Session::schedule(
            id: SessionId::generate(),
            experienceId: ExperienceId::generate(),
            date: new \DateTimeImmutable('+1 day'),
            maxCapacity: 0,
            price: 1000,
        );
    }

    public function test_has_started_returns_true_for_past_session(): void
    {
        $session = Session::schedule(
            id: SessionId::generate(),
            experienceId: ExperienceId::generate(),
            date: new \DateTimeImmutable('+1 second'),
            maxCapacity: 10,
            price: 1000,
        );

        sleep(2);

        $this->assertTrue($session->hasStarted());
    }

    public function test_cannot_be_cancelled_less_than_24_hours_before_start(): void
    {
        $session = Session::schedule(
            id: SessionId::generate(),
            experienceId: ExperienceId::generate(),
            date: new \DateTimeImmutable('+12 hours'),
            maxCapacity: 10,
            price: 1000,
        );

        $this->assertFalse($session->canBeCancelledAt(new \DateTimeImmutable()));
    }

    public function test_can_be_cancelled_more_than_24_hours_before_start(): void
    {
        $session = Session::schedule(
            id: SessionId::generate(),
            experienceId: ExperienceId::generate(),
            date: new \DateTimeImmutable('+48 hours'),
            maxCapacity: 10,
            price: 1000,
        );

        $this->assertTrue($session->canBeCancelledAt(new \DateTimeImmutable()));
    }
}
<?php

declare(strict_types=1);

namespace App\Tests\Reservation\Presentation\Http;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CancelReservationControllerTest extends WebTestCase
{
    private function createReservation($client, string $sessionDateModifier = '+3 days', int $seats = 2): string
    {
        $client->request(
            'POST',
            '/experiences',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'title' => 'Test Experience',
                'description' => 'Test Description',
                'providerId' => 'provider-test',
            ]),
        );
        $experienceId = json_decode($client->getResponse()->getContent(), true)['experienceId'];

        $client->request(
            'POST',
            '/sessions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'experienceId' => $experienceId,
                'date' => (new \DateTimeImmutable($sessionDateModifier))->format('Y-m-d'),
                'maxCapacity' => 10,
                'price' => 1000,
            ]),
        );
        $sessionId = json_decode($client->getResponse()->getContent(), true)['sessionId'];

        $client->request(
            'POST',
            '/reservations',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'sessionId' => $sessionId,
                'userId' => 'user-test',
                'seats' => $seats,
            ]),
        );

        return json_decode($client->getResponse()->getContent(), true)['reservationId'];
    }

    public function test_can_cancel_reservation_more_than_24_hours_before_session(): void
    {
        $client = static::createClient();
        $reservationId = $this->createReservation($client, '+3 days');

        $client->request('PATCH', "/reservations/{$reservationId}/cancel");

        $this->assertSame(204, $client->getResponse()->getStatusCode());
    }

    public function test_cannot_cancel_reservation_twice(): void
    {
        $client = static::createClient();
        $reservationId = $this->createReservation($client, '+3 days');

        $client->request('PATCH', "/reservations/{$reservationId}/cancel");
        $this->assertSame(204, $client->getResponse()->getStatusCode());

        $client->request('PATCH', "/reservations/{$reservationId}/cancel");
        $this->assertSame(409, $client->getResponse()->getStatusCode());
    }
}
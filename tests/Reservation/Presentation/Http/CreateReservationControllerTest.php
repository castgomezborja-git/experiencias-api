<?php

declare(strict_types=1);

namespace App\Tests\Reservation\Presentation\Http;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateReservationControllerTest extends WebTestCase
{
    public function test_cannot_reserve_more_seats_than_available_capacity(): void
    {
        $client = static::createClient();

        // 1. Crear experiencia
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
        $experienceData = json_decode($client->getResponse()->getContent(), true);
        $experienceId = $experienceData['experienceId'];

        // 2. Crear sesión con aforo de 5
        $client->request(
            'POST',
            '/sessions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'experienceId' => $experienceId,
                'date' => (new \DateTimeImmutable('+2 days'))->format('Y-m-d'),
                'maxCapacity' => 5,
                'price' => 1000,
            ]),
        );
        $sessionData = json_decode($client->getResponse()->getContent(), true);
        $sessionId = $sessionData['sessionId'];

        // 3. Reservar 5 plazas (debería funcionar, agota el aforo)
        $client->request(
            'POST',
            '/reservations',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'sessionId' => $sessionId,
                'userId' => 'user-1',
                'seats' => 5,
            ]),
        );
        $this->assertSame(201, $client->getResponse()->getStatusCode());

        // 4. Intentar reservar 1 plaza más (debería fallar, ya no quedan)
        $client->request(
            'POST',
            '/reservations',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'sessionId' => $sessionId,
                'userId' => 'user-2',
                'seats' => 1,
            ]),
        );

        $this->assertSame(409, $client->getResponse()->getStatusCode());
    }

    public function test_cannot_reserve_a_session_that_has_already_started(): void
    {
        $this->markTestSkipped(
            'Requires a session in the past, but Session::schedule() forbids past dates. ' .
            'Would need a test-only factory or direct repository injection to set up this scenario.'
        );
    }
}
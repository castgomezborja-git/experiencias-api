<?php

declare(strict_types=1);

namespace App\Tests\Experience\Presentation\Http;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateSessionControllerTest extends WebTestCase
{
    public function test_cannot_create_two_sessions_for_same_experience_on_same_day(): void
    {
        $client = static::createClient();

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

        $date = (new \DateTimeImmutable('+3 days'))->format('Y-m-d');

        // Primera sesión: debe funcionar
        $client->request(
            'POST',
            '/sessions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'experienceId' => $experienceId,
                'date' => $date,
                'maxCapacity' => 10,
                'price' => 1000,
            ]),
        );
        $this->assertSame(201, $client->getResponse()->getStatusCode());

        // Segunda sesión, mismo día: debe fallar con 409
        $client->request(
            'POST',
            '/sessions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'experienceId' => $experienceId,
                'date' => $date,
                'maxCapacity' => 5,
                'price' => 500,
            ]),
        );
        $this->assertSame(409, $client->getResponse()->getStatusCode());
    }

    public function test_cannot_create_session_in_the_past(): void
    {
        $client = static::createClient();

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

        $client->request(
            'POST',
            '/sessions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'experienceId' => $experienceId,
                'date' => (new \DateTimeImmutable('-1 day'))->format('Y-m-d'),
                'maxCapacity' => 10,
                'price' => 1000,
            ]),
        );

        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }
}
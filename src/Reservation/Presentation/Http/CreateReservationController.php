<?php

declare(strict_types=1);

namespace App\Reservation\Presentation\Http;

use App\Reservation\Application\DTO\CreateReservationCommand;
use App\Reservation\Application\UseCase\CreateReservationUseCase;
use App\Reservation\Domain\Exception\SessionAlreadyStartedException;
use App\Reservation\Domain\Exception\SessionFullyBookedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateReservationController
{
    public function __construct(
        private readonly CreateReservationUseCase $createReservationUseCase
    ) {
    }

    #[Route('/reservations', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['sessionId'], $data['userId'], $data['seats'])) {
            return new JsonResponse(
                ['error' => 'Missing required fields: sessionId, userId, seats'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $reservation = $this->createReservationUseCase->execute(new CreateReservationCommand(
                sessionId: $data['sessionId'],
                userId: $data['userId'],
                seats: $data['seats'],
            ));
        } catch (SessionAlreadyStartedException | SessionFullyBookedException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'reservationId' => $reservation->getId()->getValue(),
            'sessionId' => $reservation->getSessionId()->getValue(),
            'userId' => $reservation->getUserId()->getValue(),
            'seats' => $reservation->getSeats(),
            'totalPrice' => $reservation->getTotalPrice(),
            'status' => $reservation->getStatus()->value,
        ], Response::HTTP_CREATED);
    }
}
<?php

declare(strict_types=1);

namespace App\Reservation\Presentation\Http;

use App\Reservation\Application\DTO\CancelReservationCommand;
use App\Reservation\Application\UseCase\CancelReservationUseCase;
use App\Reservation\Domain\Exception\CancellationWindowExpiredException;
use App\Reservation\Domain\Exception\ReservationAlreadyCancelledException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CancelReservationController
{
    public function __construct(
        private readonly CancelReservationUseCase $cancelReservationUseCase
    ) {
    }

    #[Route('/reservations/{reservationId}/cancel', methods: ['PATCH'])]
    public function __invoke(string $reservationId): JsonResponse
    {

        if (trim($reservationId) === '') {
            return new JsonResponse(
                ['error' => 'Missing required fields: reservationId'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $this->cancelReservationUseCase->execute(new CancelReservationCommand(
                reservationId: $reservationId,
            ));
        } catch (CancellationWindowExpiredException | ReservationAlreadyCancelledException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
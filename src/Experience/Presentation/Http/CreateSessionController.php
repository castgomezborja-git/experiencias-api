<?php

declare(strict_types=1);

namespace App\Experience\Presentation\Http;

use App\Experience\Application\DTO\CreateSessionCommand;
use App\Experience\Application\UseCase\CreateSessionUseCase;
use App\Experience\Domain\Exception\DuplicateSessionDateException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateSessionController
{
    public function __construct(
        private readonly CreateSessionUseCase $createSessionUseCase
    ) {
    }

    #[Route('/sessions', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['experienceId'], $data['date'], $data['maxCapacity'], $data['price'])) {
            return new JsonResponse(
                ['error' => 'Missing required fields: experienceId, date, maxCapacity, price'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $session = $this->createSessionUseCase->execute(new CreateSessionCommand(
                experienceId: $data['experienceId'],
                date: $data['date'],
                maxCapacity: $data['maxCapacity'],
                price: $data['price'],
            ));
        } catch (DuplicateSessionDateException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'sessionId' => $session->getId()->getValue(),
            'experienceId' => $session->getExperienceId()->getValue(),
            'date' => $session->getDate()->format('Y-m-d H:i:s'),
            'maxCapacity' => $session->getMaxCapacity(),
            'price' => $session->getPrice(),
        ], Response::HTTP_CREATED);
    }
}
<?php

declare(strict_types=1);

namespace App\Experience\Presentation\Http;

use App\Experience\Application\DTO\RegisterExperienceCommand;
use App\Experience\Application\UseCase\RegisterExperienceUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterExperienceController
{
    public function __construct(
        private readonly RegisterExperienceUseCase $registerExperienceUseCase
    ) {
    }

    #[Route('/experiences', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'], $data['description'], $data['providerId'])) {
            return new JsonResponse(
                ['error' => 'Missing required fields: title, description, providerId'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $experience = $this->registerExperienceUseCase->execute(new RegisterExperienceCommand(
                title: $data['title'],
                description: $data['description'],
                providerId: $data['providerId'],
            ));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'experienceId' => $experience->getId()->getValue(),
            'title' => $experience->getTitle(),
            'description' => $experience->getDescription(),
            'providerId' => $experience->getProviderId()->getValue(),
        ], Response::HTTP_CREATED);
    }
}
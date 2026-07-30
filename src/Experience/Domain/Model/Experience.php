<?php

declare(strict_types=1);

namespace App\Experience\Domain\Model;

use App\Experience\Domain\Model\ValueObject\ExperienceId;
use App\Experience\Domain\Model\ValueObject\ProviderId;

final class Experience
{
    private function __construct(
        private readonly ExperienceId $id, 
        private readonly ProviderId $providerId,
        private string $title,
        private string $description,
    ) {
    }

    public function getId(): ExperienceId
    {
        return $this->id;
    }

    public function getProviderId(): ProviderId
    {
        return $this->providerId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public static function register(
        ExperienceId $id, 
        ProviderId $providerId, 
        string $title, 
        string $description
    ): self {
        
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Title cannot be empty.');
        }

        if (trim($description) === '') {
            throw new \InvalidArgumentException('Description cannot be empty.');
        }

        return new self($id, $providerId, $title, $description);
    }
}
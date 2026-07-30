<?php

declare(strict_types=1);

namespace App\Experience\Domain\Model\ValueObject;

use Symfony\Component\Uid\Uuid;

final class ExperienceId
{
    private function __construct(
        private readonly string $value
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::v4()->toRfc4122());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
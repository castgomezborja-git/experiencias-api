<?php

declare(strict_types=1);

namespace App\Experience\Infrastructure\Persistence\Type;

use App\Experience\Domain\Model\ValueObject\ExperienceId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class ExperienceIdType extends Type
{
    public const NAME = 'experience_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'VARCHAR(36)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ExperienceId
    {
        if ($value === null) {
            return null;
        }

        return ExperienceId::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof ExperienceId) {
            throw new \InvalidArgumentException('Expected instance of ExperienceId.');
        }

        return $value->getValue();
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
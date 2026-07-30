<?php

declare(strict_types=1);

namespace App\Reservation\Infrastructure\Persistence\Type;

use App\Reservation\Domain\Model\ValueObject\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class UserIdType extends Type
{
    public const NAME = 'user_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'VARCHAR(36)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserId
    {
        if ($value === null) {
            return null;
        }

        return UserId::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof UserId) {
            throw new \InvalidArgumentException('Expected instance of UserId.');
        }

        return $value->getValue();
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
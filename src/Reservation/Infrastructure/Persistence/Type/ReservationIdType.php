<?php

declare(strict_types=1);

namespace App\Reservation\Infrastructure\Persistence\Type;

use App\Reservation\Domain\Model\ValueObject\ReservationId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class ReservationIdType extends Type
{
    public const NAME = 'reservation_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'VARCHAR(36)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ReservationId
    {
        if ($value === null) {
            return null;
        }

        return ReservationId::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof ReservationId) {
            throw new \InvalidArgumentException('Expected instance of ReservationId.');
        }

        return $value->getValue();
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
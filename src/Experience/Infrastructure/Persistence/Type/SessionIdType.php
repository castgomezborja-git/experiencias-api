<?php

declare(strict_types=1);

namespace App\Experience\Infrastructure\Persistence\Type;

use App\Experience\Domain\Model\ValueObject\SessionId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class SessionIdType extends Type
{
    public const NAME = 'session_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'VARCHAR(36)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?SessionId
    {
        if ($value === null) {
            return null;
        }

        return SessionId::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof SessionId) {
            throw new \InvalidArgumentException('Expected instance of SessionId.');
        }

        return $value->getValue();
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
<?php

declare(strict_types=1);

namespace App\Experience\Infrastructure\Persistence\Type;

use App\Experience\Domain\Model\ValueObject\ProviderId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class ProviderIdType extends Type
{
    public const NAME = 'provider_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'VARCHAR(36)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ProviderId
    {
        if ($value === null) {
            return null;
        }

        return ProviderId::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof ProviderId) {
            throw new \InvalidArgumentException('Expected instance of ProviderId.');
        }

        return $value->getValue();
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
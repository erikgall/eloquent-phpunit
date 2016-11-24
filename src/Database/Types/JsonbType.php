<?php

namespace EGALL\EloquentPHPUnit\Database\Types;

use Doctrine\DBAL\Types\JsonArrayType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Jsonb database column type.
 *
 * @author Erik Galloway <erik@fliplearning.com>
 */
class JsonbType extends JsonArrayType
{
    /**
     * Get the sql declartion string.
     *
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'JSONB';
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return 'jsonb';
    }
}

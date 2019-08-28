<?php

namespace Karomap\GeoLaravel\DoctrineTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class GeometryType extends Type
{
    const NAME = 'geometry';

    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return static::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function canRequireSQLConversion()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValueSQL($sqlExpr,  $platform)
    {
        return "ST_AsText('$sqlExpr')";
    }

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        $srid = config('geo.srid', 4326);
        return "ST_GeomFromText('$sqlExpr', $srid)";
    }
}

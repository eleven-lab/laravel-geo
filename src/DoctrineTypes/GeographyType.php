<?php

namespace Karomap\GeoLaravel\DoctrineTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class GeographyType extends GeometryType
{
    const NAME = 'geography';

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        $srid = config('geo.srid', 4326);

        return "ST_GeogFromText('SRID=$srid;$sqlExpr')";
    }
}

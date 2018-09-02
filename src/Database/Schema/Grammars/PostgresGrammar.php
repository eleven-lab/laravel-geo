<?php

namespace Karomap\GeoLaravel\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\PostgresGrammar as IlluminatePostgresGrammar;
use Illuminate\Support\Fluent;

/**
 * Extended version of MySqlGrammar with
 * support of 'set' data type
 */
class PostgresGrammar extends IlluminatePostgresGrammar
{
    /**
     * Create the column definition for a spatial Geometry type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @throws \RuntimeException
     */
    protected function typeGeometry(Fluent $column)
    {
        return $this->formatPostGisType('geometry');
    }

    /**
     * Create the column definition for a spatial Point type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typePoint(Fluent $column)
    {
        return $this->formatPostGisType('point');
    }

    /**
     * Create the column definition for a spatial LineString type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeLineString(Fluent $column)
    {
        return $this->formatPostGisType('linestring');
    }

    /**
     * Create the column definition for a spatial Polygon type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typePolygon(Fluent $column)
    {
        return $this->formatPostGisType('polygon');
    }

    /**
     * Create the column definition for a spatial GeometryCollection type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeGeometryCollection(Fluent $column)
    {
        return $this->formatPostGisType('geometrycollection');
    }

    /**
     * Create the column definition for a spatial MultiPoint type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMultiPoint(Fluent $column)
    {
        return $this->formatPostGisType('multipoint');
    }

    /**
     * Create the column definition for a spatial MultiLineString type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    public function typeMultiLineString(Fluent $column)
    {
        return $this->formatPostGisType('multilinestring');
    }

    /**
     * Create the column definition for a spatial MultiPolygon type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMultiPolygon(Fluent $column)
    {
        return $this->formatPostGisType('multipolygon');
    }


    private function formatPostGisType($type)
    {
        $srid = config('geo.srid', 4326);
        $column_type = config('geo.geometry', true) ? 'geometry' : 'geography';
        return "$column_type($type, $srid)";
    }
}

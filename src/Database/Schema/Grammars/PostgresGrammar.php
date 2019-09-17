<?php

namespace Karomap\GeoLaravel\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\PostgresGrammar as IlluminatePostgresGrammar;
use Illuminate\Support\Fluent;

/**
 * Extended version of PostgressGrammar with
 * support of 'set' data type.
 */
class PostgresGrammar extends IlluminatePostgresGrammar
{
    /**
     * Create the column definition for a spatial Geometry type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @throws \RuntimeException
     */
    protected function typeGeometry(Fluent $column)
    {
        return $this->formatPostGisType('geometry', $column->srid);
    }

    /**
     * Create the column definition for a spatial Point type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typePoint(Fluent $column)
    {
        return $this->formatPostGisType('point', $column->srid);
    }

    /**
     * Create the column definition for a spatial LineString type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeLineString(Fluent $column)
    {
        return $this->formatPostGisType('linestring', $column->srid);
    }

    /**
     * Create the column definition for a spatial Polygon type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typePolygon(Fluent $column)
    {
        return $this->formatPostGisType('polygon', $column->srid);
    }

    /**
     * Create the column definition for a spatial GeometryCollection type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeGeometryCollection(Fluent $column)
    {
        return $this->formatPostGisType('geometrycollection', $column->srid);
    }

    /**
     * Create the column definition for a spatial MultiPoint type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeMultiPoint(Fluent $column)
    {
        return $this->formatPostGisType('multipoint', $column->srid);
    }

    /**
     * Create the column definition for a spatial MultiLineString type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    public function typeMultiLineString(Fluent $column)
    {
        return $this->formatPostGisType('multilinestring', $column->srid);
    }

    /**
     * Create the column definition for a spatial MultiPolygon type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeMultiPolygon(Fluent $column)
    {
        return $this->formatPostGisType('multipolygon', $column->srid);
    }

    /**
     * Format column type for PostGIS.
     *
     * @param  string   $type
     * @param  int|null $srid
     * @return string
     */
    private function formatPostGisType($type, $srid = null)
    {
        $srid = $srid ?? config('geo.srid', 4326);
        $column_type = config('geo.geometry', true) ? 'geometry' : 'geography';

        return "$column_type($type, $srid)";
    }
}

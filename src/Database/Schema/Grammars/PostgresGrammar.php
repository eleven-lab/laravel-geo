<?php

namespace ElevenLab\GeoLaravel\Database\Schema\Grammars;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\PostgresGrammar as IlluminatePostgresGrammar;

/**
 * Extended version of MySqlGrammar with
 * support of 'set' data type
 */
class PostgresGrammar extends IlluminatePostgresGrammar
{
    /**
     * Create the column definition for a Point type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typePoint(Fluent $column)
    {
        return $this->formatPostGisType($column->type);
    }

    /**
     * Create the column definition for a Multipoint type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMultipoint(Fluent $column)
    {
        return $this->formatPostGisType($column->type);
    }

    /**
     * Create the column definition for a Linestring type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeLinestring(Fluent $column)
    {
        return $this->formatPostGisType($column->type);
    }

    /**
     * Create the column definition for a Polygon type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typePolygon(Fluent $column)
    {
        return $this->formatPostGisType($column->type);
    }

    /**
     * Create the column definition for a MultiPolygon type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMultipolygon(Fluent $column)
    {
        return $this->formatPostGisType($column->type);
    }

    /**
     * Create the column definition for a GeometryCollection type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeGeometrycollection(Fluent $column)
    {
        return $this->formatPostGisType($column->type);
    }

    /**
     * Create the column definition for a MultiPolygon type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @throws \Exception
     */
    protected function typeGeometry(Fluent $column)
    {
        throw new \Exception('Geometry data type not supported for current database engine.');
    }


    private function formatPostGisType($type)
    {
        return "GEOGRAPHY($type, 4326)";
    }
}
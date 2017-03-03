<?php

namespace ElevenLab\GeoLaravel\Database\Schema\Grammars;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as IlluminateMySqlGrammar;

/**
 *
 * Extended version of MySqlGrammar with
 * support of 'set' data type
 */
class MySqlGrammar extends IlluminateMySqlGrammar
{
    /**
     * Create the column definition for a Point type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typePoint(Fluent $column)
    {
        return $column->type;
    }

    /**
     * Create the column definition for a Multipoint type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMultipoint(Fluent $column)
    {
        return $column->type;
    }

    /**
     * Create the column definition for a Linestring type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeLinestring(Fluent $column)
    {
        return $column->type;
    }

    /**
     * Create the column definition for a Polygon type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typePolygon(Fluent $column)
    {
        return $column->type;
    }

    /**
     * Create the column definition for a MultiPolygon type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMultipolygon(Fluent $column)
    {
        return $column->type;
    }

    /**
     * Create the column definition for a GeometryCollection type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeGeometrycollection(Fluent $column)
    {
        return $column->type;
    }

    /**
     * Create the column definition for a Geometry type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeGeometry(Fluent $column)
    {
        return $column->type;
    }
}
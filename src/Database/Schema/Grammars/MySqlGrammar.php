<?php

namespace Karomap\GeoLaravel\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\MySqlGrammar as IlluminateMySqlGrammar;
use Illuminate\Support\Fluent;

/**
 * Extended version of MySqlGrammar with
 * support of 'set' data type.
 */
class MySqlGrammar extends IlluminateMySqlGrammar
{
    /**
     * Create the column definition for a spatial Geometry type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    public function typeGeometry(Fluent $column)
    {
        return 'geometry';
    }

    /**
     * Create the column definition for a spatial Point type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    public function typePoint(Fluent $column)
    {
        return 'point';
    }

    /**
     * Create the column definition for a spatial LineString type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    public function typeLineString(Fluent $column)
    {
        return 'linestring';
    }

    /**
     * Create the column definition for a spatial Polygon type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    public function typePolygon(Fluent $column)
    {
        return 'polygon';
    }

    /**
     * Create the column definition for a spatial GeometryCollection type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    public function typeGeometryCollection(Fluent $column)
    {
        return 'geometrycollection';
    }

    /**
     * Create the column definition for a spatial MultiPoint type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    public function typeMultiPoint(Fluent $column)
    {
        return 'multipoint';
    }

    /**
     * Create the column definition for a spatial MultiLineString type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    public function typeMultiLineString(Fluent $column)
    {
        return 'multilinestring';
    }

    /**
     * Create the column definition for a spatial MultiPolygon type.
     *
     * @param  \Illuminate\Support\Fluent $column
     * @return string
     */
    public function typeMultiPolygon(Fluent $column)
    {
        return 'multipolygon';
    }
}

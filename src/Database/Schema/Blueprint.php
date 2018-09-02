<?php

namespace Karomap\GeoLaravel\Database\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;

/**
 * Extended version of Blueprint with
 * support of geo data type
 * for laravel below 5.5
 */
class Blueprint extends IlluminateBlueprint
{
    /**
     * Create a new geometry column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Fluent
     */
    public function geometry($column)
    {
        return $this->addColumn('geometry', $column);
    }

    /**
     * Create a new point column on the table.
     *
     * @param  string  $column
     * @param  null|int  $srid
     * @return \Illuminate\Support\Fluent
     */
    public function point($column, $srid = null)
    {
        return $this->addColumn('point', $column, compact('srid'));
    }

    /**
     * Create a new linestring column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Fluent
     */
    public function lineString($column)
    {
        return $this->addColumn('linestring', $column);
    }

    /**
     * Create a new polygon column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Fluent
     */
    public function polygon($column)
    {
        return $this->addColumn('polygon', $column);
    }

    /**
     * Create a new geometrycollection column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Fluent
     */
    public function geometryCollection($column)
    {
        return $this->addColumn('geometrycollection', $column);
    }

    /**
     * Create a new multipoint column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Fluent
     */
    public function multiPoint($column)
    {
        return $this->addColumn('multipoint', $column);
    }

    /**
     * Create a new multilinestring column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Fluent
     */
    public function multiLineString($column)
    {
        return $this->addColumn('multilinestring', $column);
    }

    /**
     * Create a new multipolygon column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Fluent
     */
    public function multiPolygon($column)
    {
        return $this->addColumn('multipolygon', $column);
    }
}

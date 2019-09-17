<?php

namespace Karomap\GeoLaravel\Database\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;

/**
 * Extended version of Blueprint with
 * support of geo data type
 * for laravel below 5.5.
 */
class Blueprint extends IlluminateBlueprint
{
    /**
     * Create a new geometry column on the table.
     *
     * @param  string                     $column
     * @param  int|null                   $srid
     * @return \Illuminate\Support\Fluent
     */
    public function geometry($column, $srid = null)
    {
        return $this->addColumn('geometry', $column, compact('srid'));
    }

    /**
     * Create a new point column on the table.
     *
     * @param  string                     $column
     * @param  int|null                   $srid
     * @return \Illuminate\Support\Fluent
     */
    public function point($column, $srid = null)
    {
        return $this->addColumn('point', $column, compact('srid'));
    }

    /**
     * Create a new linestring column on the table.
     *
     * @param  string                     $column
     * @param  int|null                   $srid
     * @return \Illuminate\Support\Fluent
     */
    public function lineString($column, $srid = null)
    {
        return $this->addColumn('linestring', $column, compact('srid'));
    }

    /**
     * Create a new polygon column on the table.
     *
     * @param  string                     $column
     * @param  int|null                   $srid
     * @return \Illuminate\Support\Fluent
     */
    public function polygon($column, $srid = null)
    {
        return $this->addColumn('polygon', $column, compact('srid'));
    }

    /**
     * Create a new geometrycollection column on the table.
     *
     * @param  string                     $column
     * @param  int|null                   $srid
     * @return \Illuminate\Support\Fluent
     */
    public function geometryCollection($column, $srid = null)
    {
        return $this->addColumn('geometrycollection', $column, compact('srid'));
    }

    /**
     * Create a new multipoint column on the table.
     *
     * @param  string                     $column
     * @param  int|null                   $srid
     * @return \Illuminate\Support\Fluent
     */
    public function multiPoint($column, $srid = null)
    {
        return $this->addColumn('multipoint', $column, compact('srid'));
    }

    /**
     * Create a new multilinestring column on the table.
     *
     * @param  string                     $column
     * @param  int|null                   $srid
     * @return \Illuminate\Support\Fluent
     */
    public function multiLineString($column, $srid = null)
    {
        return $this->addColumn('multilinestring', $column, compact('srid'));
    }

    /**
     * Create a new multipolygon column on the table.
     *
     * @param  string                     $column
     * @param  int|null                   $srid
     * @return \Illuminate\Support\Fluent
     */
    public function multiPolygon($column, $srid = null)
    {
        return $this->addColumn('multipolygon', $column, compact('srid'));
    }
}

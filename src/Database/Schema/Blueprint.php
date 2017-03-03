<?php namespace ElevenLab\GeoLaravel\Database\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;

/**
 * Extended version of Blueprint with
 * support of geo data type
 *
 */
class Blueprint extends IlluminateBlueprint
{

    /**
     * @param $column
     * @return \Illuminate\Support\Fluent
     */
    public function point($column)
    {
        return $this->addColumn('point', $column);
    }

    /**
     * @param $column
     * @return \Illuminate\Support\Fluent
     */
    public function multipoint($column)
    {
        return $this->addColumn('multipoint', $column);
    }

    /**
     * @param $column
     * @return \Illuminate\Support\Fluent
     */
    public function linestring($column)
    {
        return $this->addColumn('linestring', $column);
    }

    /**
     * @param $column
     * @return \Illuminate\Support\Fluent
     */
    public function multilinestring($column)
    {
        return $this->addColumn('multilinestring', $column);
    }

    /**
     * @param $column
     * @return \Illuminate\Support\Fluent
     */
    public function polygon($column)
    {
        return $this->addColumn('polygon', $column);
    }

    /**
     * @param $column
     * @return \Illuminate\Support\Fluent
     */
    public function multipolygon($column)
    {
        return $this->addColumn('multipolygon', $column);
    }

    /**
     * @param $column
     * @return \Illuminate\Support\Fluent
     */
    public function geometrycollection($column)
    {
        return $this->addColumn('geometrycollection', $column);
    }

    /**
     * @param $column
     * @return \Illuminate\Support\Fluent
     */
    public function geometry($column)
    {
        return $this->addColumn('geometry', $column);
    }


}
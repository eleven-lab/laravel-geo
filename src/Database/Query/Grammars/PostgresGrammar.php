<?php

namespace Karomap\GeoLaravel\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\PostgresGrammar as IlluminatePostgresGrammar;
use Karomap\GeoLaravel\Database\Query\Builder;

class PostgresGrammar extends IlluminatePostgresGrammar
{
    /**
     * PostGIS schema.
     *
     * @var string
     */
    protected $postgisSchema;

    /**
     * PostgresGrammar Constructor.
     */
    public function __construct()
    {
        $this->postgisSchema = config('geo.postgis_schema', 'public');
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereEquals(Builder $query, $where)
    {
        $value = app('db.connection')->geoFromText($where['value']);

        return "{$this->postgisSchema}.ST_Equals({$this->wrap($where['column'])}::{$this->postgisSchema}.geometry, $value::{$this->postgisSchema}.geometry)";
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereNotEquals(Builder $query, $where)
    {
        return 'not '.$this->whereEquals($query, $where);
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereContains(Builder $query, $where)
    {
        $value = app('db.connection')->geoFromText($where['value']);

        return "{$this->postgisSchema}.ST_Contains({$this->wrap($where['column'])}::{$this->postgisSchema}.geometry, $value::{$this->postgisSchema}.geometry)";
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereNotContains(Builder $query, $where)
    {
        return 'not '.$this->whereContains($query, $where);
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereIntersects(Builder $query, $where)
    {
        $value = app('db.connection')->geoFromText($where['value']);

        return "{$this->postgisSchema}.ST_Intersects({$this->wrap($where['column'])}, $value)";
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereNotIntersects(Builder $query, $where)
    {
        return 'not '.$this->whereIntersects($query, $where);
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereTouches(Builder $query, $where)
    {
        $value = app('db.connection')->geoFromText($where['value']);

        return "{$this->postgisSchema}.ST_Touches({$this->wrap($where['column'])}::{$this->postgisSchema}.geometry, $value::{$this->postgisSchema}.geometry)";
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereNotTouches(Builder $query, $where)
    {
        return 'not '.$this->whereTouches($query, $where);
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereOverlaps(Builder $query, $where)
    {
        $value = app('db.connection')->geoFromText($where['value']);

        return "{$this->postgisSchema}.ST_Overlaps({$this->wrap($where['column'])}::{$this->postgisSchema}.geometry, $value::{$this->postgisSchema}.geometry)";
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereNotOverlaps(Builder $query, $where)
    {
        return 'not '.$this->whereOverlaps($query, $where);
    }
}

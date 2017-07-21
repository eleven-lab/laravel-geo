<?php

namespace ElevenLab\GeoLaravel\Database\Query\Grammars;

use ElevenLab\GeoLaravel\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar as IlluminateMySqlGrammar;

class MySqlGrammar extends IlluminateMySqlGrammar
{

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereEquals(Builder $query, $where)
    {
        return "ST_Equals({$this->wrap($where['column'])}, " . app('db.connection')->geoFromText($where['value']) . ")";
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereNotEquals(Builder $query, $where)
    {
        return "not " . $this->whereEquals($query, $where);
    }


    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereContains(Builder $query, $where)
    {
        return "ST_Contains({$this->wrap($where['column'])}, " . app('db.connection')->geoFromText($where['value']) . ")";
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereNotContains(Builder $query, $where)
    {
        return "not " . $this->whereContains($query, $where);
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereIntersects(Builder $query, $where)
    {
        return "ST_Intersects({$this->wrap($where['column'])}, " . app('db.connection')->geoFromText($where['value']) . ")";
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereNotIntersects(Builder $query, $where)
    {
        return "not " . $this->whereIntersects($query, $where);
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereTouches(Builder $query, $where)
    {
        return "ST_Touches({$this->wrap($where['column'])}, " . app('db.connection')->geoFromText($where['value']) . ")";
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereNotTouches(Builder $query, $where)
    {
        return "not " . $this->whereTouches($query, $where);
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereOverlaps(Builder $query, $where)
    {
        return "ST_Overlaps({$this->wrap($where['column'])}, " . app('db.connection')->geoFromText($where['value']) . ")";
    }

    /**
     * @param Builder $query
     * @param $where
     * @return string
     */
    public function whereNotOverlaps(Builder $query, $where)
    {
        return "not " . $this->whereOverlaps($query, $where);
    }
}
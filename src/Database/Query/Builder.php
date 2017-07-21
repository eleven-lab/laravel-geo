<?php

namespace ElevenLab\GeoLaravel\Database\Query;

use ElevenLab\PHPOGC\OGCObject;
use Illuminate\Database\Query\Builder as IlluminateBuilder;

class Builder extends IlluminateBuilder
{

    /**
     * @param $column
     * @param $value
     * @param bool $not
     * @param string $boolean
     * @return $this
     */
    public function whereEquals($column, OGCObject $value, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotEquals' : 'Equals';

        $this->wheres[] = compact('type', 'column', 'value', 'boolean');

        return $this;
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function whereNotEquals($column, OGCObject $value)
    {
        return $this->whereEquals($column, $value, 'and', true);
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function orWhereEquals($column, OGCObject $value)
    {
        return $this->whereEquals($column, $value, 'or');
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function orWhereNotEquals($column, OGCObject $value)
    {
        return $this->whereEquals($column, $value, 'or', true);
    }

    /**
     * @param $column
     * @param $value
     * @param bool $not
     * @param string $boolean
     * @return $this
     */
    public function whereContains($column, OGCObject $value, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotContains' : 'Contains';

        $this->wheres[] = compact('type', 'column', 'value', 'boolean');

        return $this;
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function whereNotContains($column, OGCObject $value)
    {
        return $this->whereContains($column, $value, 'and', true);
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function orWhereContains($column, OGCObject $value)
    {
        return $this->whereContains($column, $value, 'or');
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function orWhereNotContains($column, OGCObject $value)
    {
        return $this->whereContains($column, $value, 'or', true);
    }

    /**
     * @param $column
     * @param $value
     * @param bool $not
     * @param string $boolean
     * @return $this
     */
    public function whereIntersects($column, OGCObject $value, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotIntersects' : 'Intersects';

        $this->wheres[] = compact('type', 'column', 'value', 'boolean');

        return $this;
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function whereNotIntersects($column, OGCObject $value)
    {
        return $this->whereIntersects($column, $value, 'and', true);
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function orWhereIntersects($column, OGCObject $value)
    {
        return $this->whereIntersects($column, $value, 'or');
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function orWhereNotIntersects($column, OGCObject $value)
    {
        return $this->whereIntersects($column, $value, 'or', true);
    }

    /**
     * @param $column
     * @param $value
     * @param bool $not
     * @param string $boolean
     * @return $this
     */
    public function whereTouches($column, OGCObject $value, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotTouches' : 'Touches';

        $this->wheres[] = compact('type', 'column', 'value', 'boolean');

        return $this;
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function whereNotTouches($column, OGCObject $value)
    {
        return $this->whereTouches($column, $value, 'and', true);
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function orWhereTouches($column, OGCObject $value)
    {
        return $this->whereTouches($column, $value, 'or');
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function orWhereNotTouches($column, OGCObject $value)
    {
        return $this->whereTouches($column, $value, 'or', true);
    }

    /**
     * @param $column
     * @param $value
     * @param bool $not
     * @param string $boolean
     * @return $this
     */
    public function whereOverlaps($column, OGCObject $value, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotOverlaps' : 'Overlaps';

        $this->wheres[] = compact('type', 'column', 'value', 'boolean');

        return $this;
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function whereNotOverlaps($column, OGCObject $value)
    {
        return $this->whereOverlaps($column, $value, 'and', true);
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function orWhereOverlaps($column, OGCObject $value)
    {
        return $this->whereOverlaps($column, $value, 'or');
    }

    /**
     * @param $column
     * @param OGCObject $value
     * @return Builder
     */
    public function orWhereNotOverlaps($column, OGCObject $value)
    {
        return $this->whereOverlaps($column, $value, 'or', true);
    }

}
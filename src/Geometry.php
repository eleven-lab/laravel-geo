<?php
/**
 * Created by PhpStorm.
 * User: lorenzo
 * Date: 22/10/15
 * Time: 1.44
 */


namespace MovEax\GeoSpatial;

abstract class Geometry implements GeoInterface
{

    public function toRawQuery(){
        return "GeomFromText('" . $this->toQuery() . "')";
    }

    public function toQuery(){
        throw new \Exception('Must override in child classes');
    }

}

<?php

namespace App;

use Model as GeoModel;

class Test extends GeoModel
{
    protected $table = "Test";
    public $timestamps = false;

    protected $geometries = [
        "points" =>         ['point1', 'point2', 'point3'],
        "line_strings" =>   ['linestring1', 'linestrins2'],
        "polygons" =>       ['polygon1', 'polygon2', 'polygon3']
    ];


}

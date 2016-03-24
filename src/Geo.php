<?php
/**
 * Created by PhpStorm.
 * User: lorenzo
 * Date: 16/10/15
 * Time: 15.48
 */

namespace LorenzoGiust\GeoLaravel;

use \Carbon\Carbon ;
use LorenzoGiust\GeoLaravel\Exceptions\GeoException;
use LorenzoGiust\GeoSpatial\GeoSpatialObject;
use LorenzoGiust\GeoSpatial\Point;
use LorenzoGiust\GeoSpatial\LineString;
use LorenzoGiust\GeoSpatial\Polygon;

class Geo {


    // GEOSPATIAL HELPERS
    public static function bin2text($binary){
        return \DB::select('select AsText(0x'.bin2hex($binary).') as x')[0]->x;
    }



    // GEOMETRY OPERATION
    // TODO: aggiungere tipi di operazioni ed eventuali ritorni
    /**
     *
     *   a    b
     *  ___ ___
     * |   |   |
     * |___|___| -> ST_Intersect(a, b) = false
     *              ST_Overlaps(a, b) = true
     *              ST_Touches(a, b) = true
     *  _____
     * |    _|_
     * |   | | |
     * |___|_| |
     *     |___| -> ST_Intersect(a, b) = true
     *              ST_Overlaps(a, b) = false
     *              ST_Touches(a, b) = false
     *
     */


     /*
     *
     * @param GeoSpatialObject $g1
     * @param GeoSpatialObject $g2
     * @return array
     * @throws GeoException
     */

    public static function intersection(GeoSpatialObject $g1, GeoSpatialObject $g2)
    {
        $points = [];
        $tmp = [];

        $multipoint = \DB::select("select AsText( ST_Intersection(".Geo::toQuery($g1).",".Geo::toQuery($g2).") ) as x")[0]->x;

        if (is_null($multipoint)) return [];

        if(strpos($multipoint, "MULTIPOINT(") === 0){
            $tmp = explode(",", substr(substr($multipoint, 11), 0, -1));

        }else if(strpos($multipoint, "POINT(") === 0){
            $tmp = explode(",", substr(substr($multipoint, 6), 0, -1));

        }else{
            // TODO: rimuovere assert, debug only
            assert('ne null, ne multipoint ne point !!!');
        }

        foreach( $tmp as $point ){
            $tmp2 = explode(" ", $point);
            array_push($points, new Point($tmp2[0], $tmp2[1]));
        }
        return $points;
    }

    public static function contains(Polygon $polygon, Point $point)
    {
        return (bool)\DB::select("select ST_Contains(".Geo::toQuery($polygon).",".Geo::toQuery($point).") as x")[0]->x;
    }

    public static function intersects(GeoSpatialObject $geo1, GeoSpatialObject $geo2)
    {
        return (bool)\DB::select("select ST_Intersects(".Geo::toQuery($geo1).",".Geo::toQuery($geo2).") as x")[0]->x;
    }

    public static function touches(GeoSpatialObject $geo1, GeoSpatialObject $geo2)
    {
        return (bool)\DB::select("select ST_Touches(".Geo::toQuery($geo1).",".Geo::toQuery($geo2).") as x")[0]->x;
    }

    public static function overlaps(GeoSpatialObject $geo1, GeoSpatialObject $geo2)
    {
        return (bool)\DB::select("select ST_Overlaps(".Geo::toQuery($geo1).",".Geo::toQuery($geo2).") as x")[0]->x;
    }

    /**
     * @param Polygon $polygon
     * @return \LorenzoGiust\GeoLaravel\Point
     */
    public static function centroid(Polygon $polygon)
    {
        $centroid = \DB::select('select AsText(ST_Centroid('.Geo::toQuery($polygon).')) as x')[0]->x;
        return self::fromQuery($centroid);
    }



    /**
     * @param Polygon $polygon
     * @return \LorenzoGiust\GeoLaravel\Polygon
     */
    public static function union( Polygon $p1, Polygon $p2 )
    {
        $union = \DB::select('select AsText(ST_Union('.Geo::toQuery($p1).', '.Geo::toQuery($p2).')) as x')[0]->x;
        return self::fromQuery($union);
    }

    /*
 * POINT(x y)
 * LINESTRING( POINT(x y), POINT(x y), POINT(x y) )
 * POLYGON( LINESTRING( POINT(x y), POINT(x y), POINT(x y) ), LINESTRING( POINT(x y), POINT(x y), POINT(x y) ) )
 *
 */
    public static function toQuery(GeoSpatialObject $object)
    {
        if($object instanceof Point){
            $raw = self::pointToRawQuery($object);

        }elseif($object instanceof LineString){
            $raw = self::linestringToRawQuery($object);

        }elseif($object instanceof Polygon){
            $raw = self::polygonToRawQuery($object);

        }else{
            throw new GeoException('Not implemented');
        }

        return "GeomFromText('" . $raw . "')";
    }

    private static function pointToRawQuery(Point $point)
    {
        return "POINT(".$point.")";
    }

    private static function linestringToRawQuery(LineString $linestring)
    {
        return "LINESTRING(".$linestring.")";
    }

    private static function polygonToRawQuery(Polygon $polygon)
    {
        return "POLYGON(".$polygon.")";
    }

    public static function fromQuery($query_result)
    {
        if(stripos($query_result, "POINT") === 0 ){
            $re = "/POINT\\(([0-9.]+) ([0-9.]+)\\)/";
            preg_match_all($re, $query_result, $matches);
            return new  Point($matches[1][0], $matches[2][0]);

        }elseif(stripos($query_result, "LINESTRING") === 0 ){
            $re = "/\\(([^()]+)\\),?/";
            preg_match_all($re, $query_result, $matches);
            return new LineString($matches[0]);

        }elseif(stripos($query_result, "POLYGON") === 0 ){
            $re = "/\\(([^()]+)\\),?/";
            preg_match_all($re, $query_result, $matches);
            return new Polygon($matches[1]);

        }else
            throw new GeoException('Not implemented');
    }


}
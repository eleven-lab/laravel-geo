<?php

namespace Karomap\GeoLaravel\Database;

use CrEOF\Geo\WKB\Parser;
use ElevenLab\PHPOGC\OGCObject;
use ElevenLab\PHPOGC\DataTypes\Point;
use ElevenLab\PHPOGC\DataTypes\Polygon;
use Illuminate\Database\Query\Expression;
use Karomap\GeoLaravel\Database\Schema\MySqlBuilder;
use \Illuminate\Database\MySqlConnection as IlluminateMySqlConnection;
use Karomap\GeoLaravel\Database\Query\Grammars\MySqlGrammar as MysqlQueryGrammar;
use Karomap\GeoLaravel\Database\Schema\Grammars\MySqlGrammar as MysqlSchemaGrammar;

class MySqlConnection extends IlluminateMySqlConnection
{
    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Karomap\GeoLaravel\Database\Schema\MySqlBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new MySqlBuilder($this);
    }

    /**
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo
     * @return \Illuminate\Database\Query\Expression
     */
    public function rawGeo(OGCObject $geo)
    {
        return new Expression($this->geoFromText($geo));
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Karomap\GeoLaravel\Database\MysqlSchemaGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new MysqlSchemaGrammar);
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Karomap\GeoLaravel\Database\MysqlQueryGrammar
     */
    public function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new MysqlQueryGrammar);
    }

    /**
     * @param $raw_geo
     * @return mixed
     */
    public function fromRawToWKB($raw_geo)
    {
        return $this->select('select ST_AsWKB(0x'.bin2hex($raw_geo).') as x')[0]->x;
    }

    /**
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo
     * @return string
     */
    public function geoFromText(OGCObject $geo)
    {
        $srid = config('geo.srid', 4326);
        return "ST_GeomFromText('{$geo->toWKT()}', $srid)";
    }

    /**
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo1
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo2
     * @return OGCObject|null
     */
    public function intersection(OGCObject $geo1, OGCObject $geo2)
    {
        $intersection = $this->select("select ST_AsBinary(ST_Intersection({$this->geoFromText($geo1)},{$this->geoFromText($geo2)})) as intersection")[0]->intersection;

        if(is_null($intersection))
            return null;

        $wkb_parser = new Parser();
        return OGCObject::buildOGCObject($wkb_parser->parse($intersection));
    }

    /**
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo1
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo2
     * @return mixed|null
     */
    public function difference(OGCObject $geo1, OGCObject $geo2)
    {
        $difference = $this->select("select ST_AsBinary(ST_Difference({$this->geoFromText($geo1)},{$this->geoFromText($geo2)})) as difference")[0]->difference;

        if(is_null($difference))
            return null;

        $wkb_parser = new Parser();
        return OGCObject::buildOGCObject($wkb_parser->parse($difference));
    }

    /**
     * @param  \ElevenLab\PHPOGC\DataTypes\Polygon  $polygon
     * @param  \ElevenLab\PHPOGC\DataTypes\Point  $point
     * @return bool
     */
    public function contains(Polygon $polygon, Point $point)
    {
        return (bool)$this->select("select ST_Contains({$this->geoFromText($polygon)},{$this->geoFromText($point)}) as contains")[0]->contains;
    }

    /**
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo1
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo2
     * @return bool
     */
    public function intersects(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool)$this->select("select ST_Intersects({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as intersects")[0]->intersects;
    }

    /**
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo1
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo2
     * @return bool
     */
    public function touches(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool)$this->select("select ST_Touches({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as touches")[0]->touches;
    }

    /**
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo1
     * @param  \ElevenLab\PHPOGC\OGCObject  $geo2
     * @return bool
     */
    public function overlaps(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool)$this->select("select ST_Overlaps({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as overlaps")[0]->overlaps;
    }

    /**
     * @param  \ElevenLab\PHPOGC\DataTypes\Polygon  $polygon
     * @return mixed|null
     */
    public function centroid(Polygon $polygon)
    {
        $difference = $this->select("select ST_AsBinary(ST_Centroid({$this->geoFromText($polygon)})) as centroid")[0]->centroid;

        $wkb_parser = new Parser;
        return OGCObject::buildOGCObject($wkb_parser->parse($difference));
    }

    /**
     * @param  \ElevenLab\PHPOGC\DataTypes\Point  $p1
     * @param  \ElevenLab\PHPOGC\DataTypes\Point  $p2
     * @return string
     */
    public function distance(Point $p1, Point $p2)
    {
        $distance = $this->select("select " . $this->queryDistance($p1, $p2)->getValue() . " as distance")[0]->distance;
        return $distance;
    }

    /**
     * @param  \ElevenLab\PHPOGC\OGCObject  $g1
     * @param  \ElevenLab\PHPOGC\OGCObject  $g2
     * @return bool
     */
    public function equals(OGCObject $g1, OGCObject $g2)
    {
        return (bool)$this->select("select ST_Equals({$this->geoFromText($g1)},{$this->geoFromText($g2)}) as equals")[0]->equals;
    }

    /**
     * @param  mixed  $from
     * @param  mixed  $to
     * @return string
     */
    public function queryDistance($from, $to, $as = null)
    {
        $p1x = $from instanceof Point ? $from->lat : "ST_X($from)";
        $p1y = $from instanceof Point ? $from->lon : "ST_Y($from)";
        $p2x = $to instanceof Point ? $to->lat : "ST_X($to)";
        $p2y = $to instanceof Point ? $to->lon : "ST_Y($to)";
        $query = "( 6378137 * acos( cos( radians($p1x) ) * cos( radians($p2x) ) * cos( radians($p2y) - radians($p1y) ) + sin( radians($p1x) ) * sin(radians($p2x) ) ) )";
        return $this->raw($query . (is_null($as) ? "" : " as $as"));
    }
}

<?php

namespace ElevenLab\GeoLaravel\Database;

use CrEOF\Geo\WKB\Parser;
use ElevenLab\PHPOGC\DataTypes\Polygon;
use ElevenLab\PHPOGC\OGCObject;
use Illuminate\Database\Query\Expression;
use ElevenLab\GeoLaravel\Schema\Grammars\MySqlGrammar;

class MySqlConnection extends Connection
{
    /**
     * Get the default schema grammar instance.
     *
     * @return MySqlGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new MySqlGrammar);
    }


    /**
     * @param OGCObject $geo
     * @return string
     */
    protected function geoFromText(OGCObject $geo)
    {
        return "ST_GeomFromText('{$geo->toWKT()}')";
    }

    /**
     * @param OGCObject $geo1
     * @param OGCObject $geo2
     * @return OGCObject|null
     */
    public function intersection(OGCObject $geo1, OGCObject $geo2)
    {
        $intersection = $this->select("select ST_Intersection({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as intersection")[0]->intersection;

        if(is_null($intersection))
            return null;

        $wkb_parser = new Parser();
        return OGCObject::buildOGCObject($wkb_parser->parse($intersection));
    }

    /**
     * @param OGCObject $geo1
     * @param OGCObject $geo2
     * @return mixed|null
     */
    public function difference(OGCObject $geo1, OGCObject $geo2)
    {
        $difference = $this->select("select ST_Difference({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as difference")[0]->difference;

        if(is_null($difference))
            return null;

        $wkb_parser = new Parser();
        return OGCObject::buildOGCObject($wkb_parser->parse($difference));
    }

    /**
     * @param Polygon $polygon
     * @param Point $point
     * @return bool
     */
    public function contains(Polygon $polygon, Point $point)
    {
        return (bool)$this->select("select ST_Contains({$this->geoFromText($polygon)},{$this->geoFromText($point)}) as contains")[0]->contains;
    }

    /**
     * @param OGCObject $geo1
     * @param OGCObject $geo2
     * @return bool
     */
    public function intersects(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool)$this->select("select ST_Intersects({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as intersects")[0]->intersects;
    }

    /**
     * @param OGCObject $geo1
     * @param OGCObject $geo2
     * @return bool
     */
    public function touches(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool)$this->select("select ST_Touches({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as touches")[0]->touches;
    }

    /**
     * @param OGCObject $geo1
     * @param OGCObject $geo2
     * @return bool
     */
    public function overlaps(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool)$this->select("select ST_Overlaps({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as overlaps")[0]->overlaps;
    }

    /**
     * @param Polygon $polygon
     * @return mixed|null
     */
    public function centroid(Polygon $polygon)
    {
        $difference = $this->select("select ST_Centroid({$this->geoFromText($polygon)}) as centroid")[0]->centroid;

        $wkb_parser = new Parser;
        return OGCObject::buildOGCObject($wkb_parser->parse($difference));
    }
}

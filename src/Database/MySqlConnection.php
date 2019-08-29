<?php

namespace Karomap\GeoLaravel\Database;

use CrEOF\Geo\WKB\Parser;
use Illuminate\Database\MySqlConnection as IlluminateMySqlConnection;
use Illuminate\Database\Query\Expression;
use Karomap\GeoLaravel\Database\Query\Builder as QueryBuilder;
use Karomap\GeoLaravel\Database\Query\Grammars\MySqlGrammar as MysqlQueryGrammar;
use Karomap\GeoLaravel\Database\Schema\Grammars\MySqlGrammar as MysqlSchemaGrammar;
use Karomap\GeoLaravel\Database\Schema\MySqlBuilder;
use Karomap\PHPOGC\DataTypes\Point;
use Karomap\PHPOGC\DataTypes\Polygon;
use Karomap\PHPOGC\OGCObject;

class MySqlConnection extends IlluminateMySqlConnection
{
    /**
     * {@inheritDoc}
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);
        $customDoctrineTypes = [
            'GeometryType',
            'PointType',
            'MultiPointType',
            'LineStringType',
            'MultiLineStringType',
            'PolygonType',
            'MultiPolygonType',
            'GeometryCollectionType',
            'GeomCollectionType',
        ];

        $doctrinePlatform = $this->getDoctrineSchemaManager()->getDatabasePlatform();
        foreach ($customDoctrineTypes as $customDoctrineType) {
            $typeClass = "Karomap\GeoLaravel\DoctrineTypes\\$customDoctrineType";
            $this->getSchemaBuilder()->registerCustomDoctrineType($typeClass, $typeClass::NAME, $typeClass::NAME);
            $doctrinePlatform->registerDoctrineTypeMapping($typeClass::NAME, $typeClass::NAME);
        }
    }

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
     * Get a new query builder instance.
     *
     * @return \Karomap\GeoLaravel\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $geo
     * @return \Illuminate\Database\Query\Expression
     */
    public function rawGeo(OGCObject $geo)
    {
        return new Expression($this->geoFromText($geo));
    }

    /**
     * @param $raw_geo
     * @return mixed
     */
    public function fromRawToWKB($raw_geo)
    {
        return $this->select('select ST_AsWKB(0x' . bin2hex($raw_geo) . ') as x')[0]->x;
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $geo
     * @return string
     */
    public function geoFromText(OGCObject $geo)
    {
        $srid = $geo->srid ?? config('geo.srid', 4326);
        return "ST_GeomFromText('{$geo->toWKT()}', $srid)";
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $geo1
     * @param  \Karomap\PHPOGC\OGCObject  $geo2
     * @return OGCObject|null
     */
    public function intersection(OGCObject $geo1, OGCObject $geo2)
    {
        $intersection = $this->select("select ST_AsBinary(ST_Intersection({$this->geoFromText($geo1)},{$this->geoFromText($geo2)})) as intersection")[0]->intersection;

        if (is_null($intersection))
            return null;

        $wkb_parser = new Parser();
        return OGCObject::buildOGCObject($wkb_parser->parse($intersection));
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $geo1
     * @param  \Karomap\PHPOGC\OGCObject  $geo2
     * @return mixed|null
     */
    public function difference(OGCObject $geo1, OGCObject $geo2)
    {
        $difference = $this->select("select ST_AsBinary(ST_Difference({$this->geoFromText($geo1)},{$this->geoFromText($geo2)})) as difference")[0]->difference;

        if (is_null($difference))
            return null;

        $wkb_parser = new Parser();
        return OGCObject::buildOGCObject($wkb_parser->parse($difference));
    }

    /**
     * @param  \Karomap\PHPOGC\DataTypes\Polygon  $polygon
     * @param  \Karomap\PHPOGC\DataTypes\Point  $point
     * @return bool
     */
    public function contains(Polygon $polygon, Point $point)
    {
        return (bool) $this->select("select ST_Contains({$this->geoFromText($polygon)},{$this->geoFromText($point)}) as contains")[0]->contains;
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $geo1
     * @param  \Karomap\PHPOGC\OGCObject  $geo2
     * @return bool
     */
    public function intersects(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool) $this->select("select ST_Intersects({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as intersects")[0]->intersects;
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $geo1
     * @param  \Karomap\PHPOGC\OGCObject  $geo2
     * @return bool
     */
    public function touches(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool) $this->select("select ST_Touches({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as touches")[0]->touches;
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $geo1
     * @param  \Karomap\PHPOGC\OGCObject  $geo2
     * @return bool
     */
    public function overlaps(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool) $this->select("select ST_Overlaps({$this->geoFromText($geo1)},{$this->geoFromText($geo2)}) as overlaps")[0]->overlaps;
    }

    /**
     * @param  \Karomap\PHPOGC\DataTypes\Polygon  $polygon
     * @return mixed|null
     */
    public function centroid(Polygon $polygon)
    {
        $difference = $this->select("select ST_AsBinary(ST_Centroid({$this->geoFromText($polygon)})) as centroid")[0]->centroid;

        $wkb_parser = new Parser;
        return OGCObject::buildOGCObject($wkb_parser->parse($difference));
    }

    /**
     * @param  \Karomap\PHPOGC\DataTypes\Point  $p1
     * @param  \Karomap\PHPOGC\DataTypes\Point  $p2
     * @return string
     */
    public function distance(Point $p1, Point $p2)
    {
        $distance = $this->select("select " . $this->queryDistance($p1, $p2)->getValue() . " as distance")[0]->distance;
        return $distance;
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $g1
     * @param  \Karomap\PHPOGC\OGCObject  $g2
     * @return bool
     */
    public function equals(OGCObject $g1, OGCObject $g2)
    {
        return (bool) $this->select("select ST_Equals({$this->geoFromText($g1)},{$this->geoFromText($g2)}) as equals")[0]->equals;
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

    /**
     * Get column SRID
     *
     * @param string $table
     * @param string $column
     * @return int
     */
    public function getSRID($table, $column)
    {
        $result = $this->table('information_schema.ST_GEOMETRY_COLUMNS')
            ->select('SRS_ID')
            ->where('TABLE_SCHEMA', $this->database)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->first();
        return $result ? $result->SRS_ID : config('geo.srid', 4326);
    }
}

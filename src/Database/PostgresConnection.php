<?php

namespace Karomap\GeoLaravel\Database;

use CrEOF\Geo\WKB\Parser;
use Illuminate\Database\PostgresConnection as IlluminatePostgresConnection;
use Illuminate\Database\Query\Expression;
use Karomap\GeoLaravel\Database\Query\Builder as QueryBuilder;
use Karomap\GeoLaravel\Database\Query\Grammars\PostgresGrammar as PostgresQueryGrammar;
use Karomap\GeoLaravel\Database\Schema\Grammars\PostgresGrammar as PostgresSchemaGrammar;
use Karomap\GeoLaravel\Database\Schema\PostgresBuilder;
use Karomap\PHPOGC\DataTypes\Point;
use Karomap\PHPOGC\DataTypes\Polygon;
use Karomap\PHPOGC\OGCObject;

class PostgresConnection extends IlluminatePostgresConnection
{
    /**
     * @param mixed $pdo
     * @param string $database
     * @param string $tablePrefix
     * @param array $config
     *
     * @return void
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);
        $customDoctrineTypes = [
            'GeometryType',
            'GeographyType',
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
     * @return \Karomap\GeoLaravel\Database\Schema\PostgresBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new PostgresBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Karomap\GeoLaravel\Database\PostgresSchemaGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new PostgresSchemaGrammar);
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Karomap\GeoLaravel\Database\PostgresQueryGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new PostgresQueryGrammar);
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
     * Get a new raw geo query expression.
     *
     * @param  \Karomap\PHPOGC\OGCObject  $geo
     *
     * @return \Illuminate\Database\Query\Expression
     */
    public function rawGeo(OGCObject $geo)
    {
        $geometry = config('geo.geometry', true);
        return new Expression($this->geoFromText($geo, $geometry));
    }

    /**
     * Convert raw database value to WKB.
     *
     * @param  mixed  $raw_geo
     * @return mixed
     */
    public function fromRawToWKB($raw_geo)
    {
        return $raw_geo; // no need to manipulate, raw postgres geometry are WKB
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $geo
     * @param  bool  $geometry
     * @return string
     */
    public function geoFromText(OGCObject $geo, $geometry = true)
    {
        $srid = $geo->srid ?? config('geo.srid', 4326);

        if ($geometry) {
            return "ST_GeomFromText('{$geo->toWKT()}', $srid)";
        }

        return "ST_GeogFromText('SRID=$srid;{$geo->toWKT()}')";
    }

    /**
     * Get intersection from 2 geometries.
     *
     * @param  \Karomap\PHPOGC\OGCObject  $geo1
     * @param  \Karomap\PHPOGC\OGCObject  $geo2
     * @return \Karomap\PHPOGC\OGCObject|null
     */
    public function intersection(OGCObject $geo1, OGCObject $geo2)
    {
        $intersection = $this->select("select ST_AsBinary(ST_Intersection({$this->geoFromText($geo1)}::geometry,{$this->geoFromText($geo2)}::geometry)) as intersection")[0]->intersection;

        if (is_null($intersection))
            return null;

        $wkb_parser = new Parser;
        return OGCObject::buildOGCObject($wkb_parser->parse(stream_get_contents($intersection)));
    }

    /**
     * Get difference from 2 geometries.
     *
     * @param  \Karomap\PHPOGC\OGCObject  $geo1
     * @param  \Karomap\PHPOGC\OGCObject  $geo2
     * @return mixed|null
     */
    public function difference(OGCObject $geo1, OGCObject $geo2)
    {
        $difference = $this->select("select ST_AsBinary(ST_Difference({$this->geoFromText($geo1)}::geometry,{$this->geoFromText($geo2)}::geometry)) as difference")[0]->difference;

        if (is_null($difference))
            return null;

        $wkb_parser = new Parser;
        return OGCObject::buildOGCObject($wkb_parser->parse(stream_get_contents($difference)));
    }

    /**
     * Check wether a polygon contains a point.
     *
     * @param  \Karomap\PHPOGC\DataTypes\Polygon  $polygon
     * @param  \Karomap\PHPOGC\DataTypes\Point  $point
     * @return bool
     */
    public function contains(Polygon $polygon, Point $point)
    {
        return (bool) $this->select("select ST_Contains({$this->geoFromText($polygon)}::geometry,{$this->geoFromText($point)}::geometry) as contains")[0]->contains;
    }

    /**
     * Check wether geometry 1 intersects geometry 2.
     *
     * @param  \Karomap\PHPOGC\OGCObject  $geo1
     * @param  \Karomap\PHPOGC\OGCObject  $geo2
     * @return bool
     */
    public function intersects(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool) $this->select("select ST_Intersects({$this->geoFromText($geo1)}::geometry,{$this->geoFromText($geo2)}::geometry) as intersects")[0]->intersects;
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $geo1
     * @param  \Karomap\PHPOGC\OGCObject  $geo2
     * @return bool
     */
    public function touches(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool) $this->select("select ST_Touches({$this->geoFromText($geo1)}::geometry,{$this->geoFromText($geo2)}::geometry) as touches")[0]->touches;
    }

    /**
     * @param  \Karomap\PHPOGC\OGCObject  $geo1
     * @param  \Karomap\PHPOGC\OGCObject  $geo2
     * @return bool
     */
    public function overlaps(OGCObject $geo1, OGCObject $geo2)
    {
        return (bool) $this->select("select ST_Overlaps({$this->geoFromText($geo1)}::geometry,{$this->geoFromText($geo2)}::geometry) as overlaps")[0]->overlaps;
    }

    /**
     * @param  \Karomap\PHPOGC\DataTypes\Polygon  $polygon
     * @return mixed|null
     */
    public function centroid(Polygon $polygon)
    {
        $difference = $this->select("select ST_AsBinary(ST_Centroid({$this->geoFromText($polygon)}::geometry)) as centroid")[0]->centroid;

        $wkb_parser = new Parser;
        return OGCObject::buildOGCObject($wkb_parser->parse(stream_get_contents($difference)));
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
        return (bool) $this->select("select ST_Equals({$this->geoFromText($g1)}::geometry,{$this->geoFromText($g2)}::geometry) as equals")[0]->equals;
    }

    /**
     * @param  mixed  $from
     * @param  mixed  $to
     * @param  string|null  $as
     * @return string
     */
    public function queryDistance($from, $to, $as = null)
    {
        $p1 = $from instanceof Point ? $this->geoFromText($from) : $from;
        $p2 = $to instanceof Point ? $this->geoFromText($to) : $to;
        $query = "ST_distance_spheroid($p1::geometry, $p2::geometry, 'SPHEROID[\"WGS 84\",6378137,298.257223563]')";
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
        $geometry = config('geo.geometry', true);
        $tableRef = $geometry ? 'geometry_columns' : 'geography_columns';
        if (!$this->hasView($tableRef)) {
            return config('geo.srid', 4326);
        }
        $result = $this->table($tableRef)
            ->select('srid')
            ->where('f_table_catalog', $this->database)
            ->where('f_table_schema', $this->getConfig('schema') ?: 'public')
            ->where('f_table_name', $table)
            ->where($geometry ? 'f_geometry_column' : 'f_geography_column', $column)
            ->first();
        return $result ? $result->srid : config('geo.srid', 4326);
    }

    /**
     * Check view exists
     *
     * @param string $name
     * @return boolean
     */
    protected function hasView($name)
    {
        return (bool) $this->table('information_schema.views')
            ->where('table_catalog', $this->database)
            ->where('table_schema', $this->getConfig('schema') ?: 'public')
            ->where('table_name', $name)
            ->count();
    }
}

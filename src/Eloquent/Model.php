<?php

namespace Karomap\GeoLaravel\Eloquent;

use CrEOF\Geo\WKT\Parser as WKTParser;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Database\Query\Expression;
use Karomap\GeoLaravel\Database\Query\Builder;
use Karomap\GeoLaravel\Exceptions\GeoException;
use Karomap\PHPOGC\OGCObject;

class Model extends IlluminateModel
{
    /**
     * Geometry attributes definitions.
     *
     * @var array
     */
    protected $geometries = [];

    /**
     * Geometry type class map.
     *
     * @var array
     */
    protected static $geoTypes = [
        'points'                => 'Karomap\PHPOGC\DataTypes\Point',
        'multipoints'           => 'Karomap\PHPOGC\DataTypes\MultiPoint',
        'linestrings'           => 'Karomap\PHPOGC\DataTypes\LineString',
        'multilinestrings'      => 'Karomap\PHPOGC\DataTypes\MultiLineString',
        'polygons'              => 'Karomap\PHPOGC\DataTypes\Polygon',
        'multipolygons'         => 'Karomap\PHPOGC\DataTypes\MultiPolygon',
        'geometrycollection'    => 'Karomap\PHPOGC\DataTypes\GeometryCollection',
    ];

    /**
     * Temporary storage.
     *
     * @var array
     */
    public $tmpGeo = [];

    public $tmpSRID = [];

    /**
     * WKT Parser.
     *
     * @var \CrEOF\Geo\WKT\Parser
     */
    protected $wktParser;

    /**
     * Override the "boot" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            self::updateGeoAttributes($model);
        });

        static::saved(function ($model) {
            self::updateGeoAttributes($model);
        });
    }

    /**
     * Get a new custom query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        return new Builder($conn, $grammar, $conn->getPostProcessor());
    }

    /**
     * Validate geometry type.
     *
     * @param  string  $geotype
     * @return bool
     */
    private static function isValidGeotype($geotype)
    {
        return (in_array($geotype, array_keys(static::$geoTypes)));
    }

    /**
     * Get geometry type of an attribute.
     *
     * @param  \Karomap\GeoLaravel\Eloquent\Model  $model
     * @param  string  $attribute
     * @return mixed
     *
     * @throws \Exception
     */
    private static function getGeoType($model, $attribute)
    {
        foreach ($model->geometries as $geometry => $attributes) {
            if (in_array($attribute, $attributes))
                return $geometry;
        }
        throw new \Exception('Given attribute has no geotype.');
    }

    /**
     * Update geometry attributes.
     *
     * @param  \Karomap\GeoLaravel\Eloquent\Model  $model
     * @return void
     *
     * @throws \Exception
     */
    public static function updateGeoAttributes($model)
    {
        if (!isset($model->geometries)) return;

        foreach ($model->geometries as $geotype => $attrnames) {
            if (!self::isValidGeotype($geotype))
                throw new \Exception('Unknown geotype: ' . $geotype);

            $classname = static::$geoTypes[$geotype];

            foreach ($attrnames as $attrname) {
                if (!isset($model->$attrname))
                    continue 2;

                $srid = $model->getSRID($attrname);

                if ($model->$attrname instanceof Expression) {
                    $model->setAttribute($attrname,  $model->tmpGeo[$attrname]);
                    unset($model->tmpGeo[$attrname]);
                } elseif ($model->$attrname instanceof $classname) {
                    $model->tmpGeo[$attrname] = $model->$attrname;
                    $model->setAttribute($attrname,  \DB::rawGeo($model->$attrname, $srid));
                } else {
                    throw new \Exception('Geometry attribute ' . $attrname . ' must be an instance of ' . $classname);
                }
            }
        }
    }

    public function getSRID($attrname)
    {
        if (!in_array($attrname, array_flatten($this->geometries)))
            throw new \Exception("Attribute $attrname is not a geometry");

        if (!isset($this->tmpSRID[$attrname])) {
            $schema = $this->getConnection()->getConfig('schema') ?? 'public';
            $query = \DB::table($this->getTable())->selectRaw("Find_SRID('$schema', '{$this->getTable()}', '$attrname')")->first();
            $this->tmpSRID[$attrname] = $query ? $query->find_srid : null;
        }

        return $this->tmpSRID[$attrname];
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     *
     * @throws \Exception
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = parent::newFromBuilder($attributes, $connection);
        if (!isset($model->geometries)) return;

        foreach ($model->geometries as $geotype => $attrnames) {
            if (!self::isValidGeotype($geotype))
                throw new \Exception('Unknown geotype: ' . $geotype);

            foreach ($attrnames as $attrname) {
                if (isset($model->$attrname)) {
                    $model->$attrname; // use the magic __get to instantiate element
                }
            }
        }
        return $model;
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (
            in_array($key, array_flatten($this->geometries)) &&     // if the attribute is a geometry
            !parent::__get($key) instanceof OGCObject &&           // if it wasn't still converted to geo object
            !parent::__get($key) instanceof Expression &&          // if it is not in DB Expression form
            !empty(parent::__get($key))                               // if it is not empty
        ) {
            $geotype = self::getGeoType($this, $key);
            $classname = self::$geoTypes[$geotype];
            $data = parent::__get($key);

            # here we have 3 possible value for $data:
            # 1) binary: we probably have a BLOB from MySQL
            # 2) hex: PgSQL gives an hex WKB output for geo data
            # 3) WKT: else
            #
            if (!ctype_print($data) or ctype_xdigit($data)) {
                $wkb = \DB::fromRawToWKB(parent::__get($key));
                $this->setAttribute($key, $classname::fromWKB($wkb));
            } else { // assuming that it is in WKT
                $this->setAttribute($key, $classname::fromWKT($data));
            }
        }

        return parent::__get($key);
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        if (!isset($this->geometries)) return $attributes;

        // Convert geometry attributes to array.
        foreach ($this->geometries as $type => $keys) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $attributes) && $attributes[$key] instanceof OGCObject) {
                    $attributes[$key] = $attributes[$key]->toArray();
                }
            }
        }

        return $attributes;
    }

    /**
     * Convert model to GeoJSON.
     *
     * @return string
     *
     * @throws \Karomap\GeoLaravel\Exceptions\GeoException
     */
    public function toGeoJson()
    {
        if (!isset($this->geometries))
            throw new GeoException('Error: No visible geometry attribute found.');

        $attributes = parent::attributesToArray();
        $geometryKeys = array_flatten($this->geometries);
        $properties = array_diff_key($attributes, array_flip($geometryKeys));
        $geometryKeys = array_intersect(array_keys($attributes), $geometryKeys);

        if (!count($geometryKeys))
            throw new GeoException('Error: No visible geometry attribute found.');

        $this->wktParser = $this->wktParser ?? new WKTParser();


        if (count($geometryKeys) > 1) {
            $geoArray = [
                'type' => 'FeatureCollection',
                'features' => [],
            ];

            foreach ($geometryKeys as $key) {
                $geoArray['features'][] = $this->buildFeature($attributes[$key], $properties);
            }
        } else {
            $geoArray = $this->buildFeature($attributes[array_values($geometryKeys)[0]], $properties);
        }

        return json_encode($geoArray);
    }

    /**
     * Convert model to GeoJSON feature.
     *
     * @param  \Karomap\PHPOGC\OGCObject $ogc  Geometry attribute to convert.
     * @param  array $properties  GeoJSON properties as array.
     * @return array  GeoJSON feature as array.
     */
    protected function buildFeature(OGCObject $ogc, array $properties)
    {
        $parsed = $this->wktParser->parse($ogc->toWKT());
        $coordinates = $parsed['value'];

        $featureArray = [
            'type' => 'Feature',
            'geometry' => [
                'type' => $parsed['type'],
                'coordinates' => $coordinates,
            ],
            'properties' => $properties,
        ];

        return $featureArray;
    }
}

<?php

namespace Karomap\GeoLaravel\Eloquent;

use CrEOF\Geo\WKT\Parser as WKTParser;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
        'points' => 'Karomap\PHPOGC\DataTypes\Point',
        'multipoints' => 'Karomap\PHPOGC\DataTypes\MultiPoint',
        'linestrings' => 'Karomap\PHPOGC\DataTypes\LineString',
        'multilinestrings' => 'Karomap\PHPOGC\DataTypes\MultiLineString',
        'polygons' => 'Karomap\PHPOGC\DataTypes\Polygon',
        'multipolygons' => 'Karomap\PHPOGC\DataTypes\MultiPolygon',
        'geometrycollection' => 'Karomap\PHPOGC\DataTypes\GeometryCollection',
    ];

    /**
     * Temporary storage.
     *
     * @var array
     */
    public $tmpGeo = [];

    /**
     * Termporary SRID storage.
     *
     * @var array
     */
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
     * Validate geometry type.
     *
     * @param  string $geotype
     * @return bool
     */
    private static function isValidGeotype($geotype)
    {
        return in_array($geotype, array_keys(static::$geoTypes));
    }

    /**
     * Update geometry attributes.
     *
     * @param  \Karomap\GeoLaravel\Eloquent\Model $model
     * @throws \Exception
     * @return void
     */
    public static function updateGeoAttributes($model)
    {
        if (empty($model->getGeometries())) {
            return;
        }

        foreach ($model->getGeometries() as $geotype => $attrnames) {
            if (!self::isValidGeotype($geotype)) {
                throw new \Exception('Unknown geotype: '.$geotype);
            }

            $classname = static::$geoTypes[$geotype];

            foreach ($attrnames as $attrname) {
                if (!isset($model->$attrname)) {
                    continue 2;
                }

                if ($model->$attrname instanceof Expression) {
                    $model->setAttribute($attrname, $model->tmpGeo[$attrname]);
                    unset($model->tmpGeo[$attrname]);
                } elseif ($model->$attrname instanceof $classname) {
                    $model->tmpGeo[$attrname] = $model->$attrname;
                    $model->setAttribute($attrname, $model->getConnection()->rawGeo($model->$attrname));
                } else {
                    throw new \Exception('Geometry attribute '.$attrname.' must be an instance of '.$classname);
                }
            }
        }
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder           $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array       $attributes
     * @param  string|null $connection
     * @throws \Exception
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        /** @var Model $model */
        $model = parent::newFromBuilder($attributes, $connection);

        foreach ($model->getGeometries() as $geotype => $attrnames) {
            if (!self::isValidGeotype($geotype)) {
                throw new \Exception('Unknown geotype: '.$geotype);
            }

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
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        $attribute = parent::__get($key);

        if (
            in_array($key, Arr::flatten($this->getGeometries())) &&
            !$attribute instanceof OGCObject &&
            !$attribute instanceof Expression &&
            !empty($attribute)
        ) {
            $geotype = self::getGeoType($this, $key);
            $classname = self::$geoTypes[$geotype];

            // here we have 3 possible value for $data:
            // 1) binary: we probably have a BLOB from MySQL
            // 2) hex: PgSQL gives an hex WKB output for geo data
            // 3) WKT: else
            //
            if (!ctype_print($attribute) || ctype_xdigit($attribute)) {
                $wkb = $this->getConnection()->fromRawToWKB($attribute);
                $ogc = $classname::fromWKB($wkb);
            } else {
                $ogc = $classname::fromWKT($attribute);
            }

            $this->setAttribute($key, $ogc);
            $attribute = $ogc;
        }

        if ($attribute instanceof OGCObject && !$attribute->srid) {
            $attribute->srid = $this->getSRID($key);
        }

        return $attribute;
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        // Convert geometry attributes to array.
        foreach ($this->getGeometries() as $keys) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $attributes) && $attributes[$key] instanceof OGCObject) {
                    $attributes[$key] = $attributes[$key]->toArray();
                }
            }
        }

        return $attributes;
    }

    /**
     * Get geometry type of an attribute.
     *
     * @param  \Karomap\GeoLaravel\Eloquent\Model $model
     * @param  string                             $attribute
     * @throws \Exception
     * @return mixed
     */
    private static function getGeoType($model, $attribute)
    {
        foreach ($model->getGeometries() as $geometry => $attributes) {
            if (in_array($attribute, $attributes)) {
                return $geometry;
            }
        }
        throw new \Exception('Given attribute has no geotype.');
    }

    /**
     * Get geometry attributes definition.
     *
     * @return array
     */
    public function getGeometries()
    {
        return is_array($this->geometries) ? $this->geometries : [];
    }

    /**
     * Get SRID for geometry attribute.
     *
     * @param  string $attrname
     * @return int
     */
    public function getSRID($attrname)
    {
        if (!in_array($attrname, Arr::flatten($this->getGeometries()))) {
            throw new \Exception("Attribute $attrname is not a geometry");
        }

        if (!isset($this->tmpSRID[$attrname])) {
            $this->tmpSRID[$attrname] = $this->getConnection()->getSRID($this->getTable(), $attrname);
        }

        return $this->tmpSRID[$attrname];
    }

    /**
     * Convert model to GeoJSON.
     *
     * @throws \Karomap\GeoLaravel\Exceptions\GeoException
     * @return string
     */
    public function toGeoJson()
    {
        if (empty($this->getGeometries())) {
            throw new GeoException('Error: No visible geometry attribute found.');
        }

        $attributes = parent::attributesToArray();
        $geometryKeys = Arr::flatten($this->getGeometries());
        $properties = array_diff_key($attributes, array_flip($geometryKeys));
        $geometryKeys = array_values(array_intersect(array_keys($attributes), $geometryKeys));

        if (!count($geometryKeys)) {
            throw new GeoException('Error: No visible geometry attribute found.');
        }

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
            $geoArray = $this->buildFeature($attributes[$geometryKeys[0]], $properties);
        }

        return json_encode($geoArray);
    }

    /**
     * Convert model to GeoJSON feature.
     *
     * @param  \Karomap\PHPOGC\OGCObject $ogc        Geometry attribute to convert.
     * @param  array                     $properties GeoJSON properties as array.
     * @return array                     GeoJSON feature as array.
     */
    protected function buildFeature($ogc, $properties)
    {
        $parsed = $this->wktParser->parse($ogc->toWKT());

        $featureArray = [
            'type' => 'Feature',
            'geometry' => [
                'type' => Str::title($parsed['type']),
                'coordinates' => $parsed['value'],
            ],
            'properties' => $properties,
        ];

        return $featureArray;
    }
}

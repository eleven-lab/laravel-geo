<?php

namespace ElevenLab\GeoLaravel\Eloquent;

use ElevenLab\GeoLaravel\Database\Query\Builder;
use ElevenLab\PHPOGC\OGCObject;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Model as IlluminateModel;

class Model extends IlluminateModel
{
    protected $geometries = [];

    protected static $geotypes = [
        'points'                => 'ElevenLab\PHPOGC\DataTypes\Point',
        'multipoints'           => 'ElevenLab\PHPOGC\DataTypes\MultiPoint',
        'linestrings'           => 'ElevenLab\PHPOGC\DataTypes\LineString',
        'multilinestrings'      => 'ElevenLab\PHPOGC\DataTypes\MultiLineString',
        'polygons'              => 'ElevenLab\PHPOGC\DataTypes\Polygon',
        'multipolygons'         => 'ElevenLab\PHPOGC\DataTypes\MultiPolygon',
        'geometrycollection'    => 'ElevenLab\PHPOGC\DataTypes\GeometryCollection'
    ];

    public $tmp_geo = [];

    /**
     * Overriding the "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function($model){
            self::updateGeoAttributes($model);
        });

        static::created(function($model){
            self::updateGeoAttributes($model);
        });

        static::updating(function($model){
            self::updateGeoAttributes($model);
        });

        static::updated(function($model){
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
     * @param $geotype
     * @return bool
     */
    private static function isValidGeotype($geotype)
    {
        return (in_array($geotype, array_keys(static::$geotypes)));
    }

    /**
     * @param $model
     * @param $attribute
     * @return mixed
     * @throws \Exception
     */
    private static function getGeoType($model, $attribute)
    {
        foreach ($model->geometries as $geometry => $attributes) {
            if(in_array($attribute, $attributes))
                return $geometry;
        }
        throw new \Exception('Given attribute has no geotype.');
    }

    /**
     * @param $model
     * @throws \Exception
     */
    public static function updateGeoAttributes($model)
    {
        if( ! isset($model->geometries) ) return;

        foreach($model->geometries as $geotype => $attrnames){
            if(!self::isValidGeotype($geotype))
                throw new \Exception('Unknown geotype: ' . $geotype);

            $classname = static::$geotypes[$geotype];

            foreach ($attrnames as $attrname){
                if( isset($model->$attrname) ){

                    if($model->$attrname instanceof Expression){
                        $model->setAttribute( $attrname,  $model->tmp_geo[$attrname] );
                        unset($model->tmp_geo[$attrname]);

                    }else if($model->$attrname instanceof $classname){
                        $model->tmp_geo[$attrname] = $model->$attrname;
                        $model->setAttribute( $attrname,  \DB::rawGeo( $model->$attrname ));

                    }else{
                        throw new \Exception('Geometry attribute ' . $attrname .' must be an instance of ' . $classname);
                    }
                }
            }
        }
    }

    /**
     * @param array $attributes
     * @param null $connection
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = parent::newFromBuilder($attributes, $connection);
        if( ! isset($model->geometries) ) return;

        foreach($model->geometries as $geotype => $attrnames) {
            if(!self::isValidGeotype($geotype))
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
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if(
            in_array($key, array_flatten($this->geometries)) &&     // if the attribute is a geometry
            ! parent::__get($key) instanceof OGCObject &&           // if it wasn't still converted to geo object
            ! parent::__get($key) instanceof Expression &&          // if it is not in DB Expression form
            parent::__get($key) != ""                               // if it is not empty
        ){
            $geotype = self::getGeoType($this, $key);
            $classname = self::$geotypes[$geotype];
            $data = parent::__get($key);

            # here we have 3 possible value for $data:
            # 1) binary: we probably have a BLOB from MySQL
            # 2) hex: PgSQL gives an hex WKB output for geo data
            # 3) WKT: else
            #
            if(!ctype_print($data) or ctype_xdigit($data)){
                $wkb = \DB::fromRawToWKB(parent::__get($key));
                $this->setAttribute($key, $classname::fromWKB($wkb));

            }else{ // assuming that it is in WKT
                $this->setAttribute($key, $classname::fromWKT($data));
            }

        }
        return parent::__get($key);
    }
}
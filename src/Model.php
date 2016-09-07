<?php


namespace LorenzoGiust\GeoLaravel;

use DB;
use Illuminate\Database\Query\Expression;
use LorenzoGiust\GeoSpatial\GeoSpatialObject;

class Model extends \Illuminate\Database\Eloquent\Model
{

    //TODO: aggiungere anche supporto per 'linestring', 'multipoint', 'multilinestring', 'multipolygon', 'geometrycollection'
    protected static $geotypes = [
        'points' => 'Point',
        'linestrings' => 'LineString',
        'polygons' => 'Polygon'
    ];

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

    public static function updateGeoAttributes($model)
    {
        if( ! isset($model->geometries) ) return;

        foreach($model->geometries as $geotype => $attrnames){
            if( ! in_array($geotype, array_keys(static::$geotypes) ))
                throw new \Exception('Unknown geotype: ' . $geotype);

            $classname = "LorenzoGiust\\GeoSpatial\\" . static::$geotypes[$geotype];
            foreach ($attrnames as $attrname){
                if( isset($model->$attrname) ){

                    if($model->$attrname instanceof Expression){
                        $model->setAttribute( $attrname, Geo::fromQuery((string)$model->$attrname) );
                    }else if($model->$attrname instanceof $classname){
                        $model->setAttribute( $attrname,  \DB::raw( Geo::toQuery($model->$attrname) ));
                    }else{
                        throw new \Exception('Geometry attribute ' . $attrname .' must be an instance of ' . $classname);
                    }
                }
            }
        }
    }

    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = parent::newFromBuilder($attributes, $connection);
        if( ! isset($model->geometries) ) return;

        foreach($model->geometries as $geotype => $attrnames) {
            if (!in_array($geotype, array_keys(static::$geotypes)))
                throw new \Exception('Unknown geotype: ' . $geotype);

            foreach ($attrnames as $attrname) {
                if (isset($model->$attrname)) {
                    $model->$attrname;
                }
            }
        }
        return $model;
    }

    public function __get($key)
    {
        if(
            in_array($key, array_flatten($this->geometries)) &&
            ! parent::__get($key) instanceof GeoSpatialObject &&
            ! parent::__get($key) instanceof Expression &&
            parent::__get($key) != ""
        ){
            $this->setAttribute( $key ,  Geo::fromQuery(Geo::bin2text(parent::__get($key))) );
        }
        return parent::__get($key);
    }
}
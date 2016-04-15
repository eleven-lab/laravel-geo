<?php


namespace LorenzoGiust\GeoLaravel;

use DB;

class Model extends \Illuminate\Database\Eloquent\Model
{

    //TODO: aggiungere anche supporto per 'linestring', 'multipoint', 'multilinestring', 'multipolygon', 'geometrycollection'
    protected static $geotypes = ['points', 'linestrings', 'polygons'];
    
    /**
     * Overriding the "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function($model){

            if( ! isset($model->geometries) ) return;

            foreach($model->geometries as $geotype => $attrnames){
                if( ! in_array($geotype, static::$geotypes ))
                    throw new \Exception('Unknown geotype: ' . $geotype);

                $classname = "LorenzoGiust\\GeoSpatial\\" . ucfirst(str_singular(camel_case($geotype)));
                foreach ($attrnames as $attrname){
                    if( isset($model->$attrname) ){
                        if(! $model->$attrname instanceof $classname)
                            throw new \Exception('Geometry attribute ' . $attrname .' must be an instance of ' . $classname);

                        $model->setAttribute( $attrname ,  DB::raw( Geo::toQuery($model->$attrname) ) );
                    }
                }
            }
        });


        static::updating(function($model){

            if( ! isset($model->geometries) ) return;

            foreach($model->geometries as $geotype => $attrnames){
                if( ! in_array($geotype, static::$geotypes ))
                    throw new \Exception('Unknown geotype: ' . $geotype);

                $classname = "LorenzoGiust\\GeoSpatial\\" . ucfirst(str_singular(camel_case($geotype)));
                foreach ($attrnames as $attrname){
                    if( isset($model->$attrname) ){
                        if(! $model->$attrname instanceof $classname)
                            throw new \Exception('Geometry attribute ' . $attrname .' must be an instance of ' . $classname);

                        $model->setAttribute( $attrname ,  DB::raw( Geo::toQuery($model->$attrname) ) );
                    }
                }
            }
        });
    }

    public function __get($key)
    {
        echo "called getter on key $key...";
        if(in_array($key, array_flatten($this->geometries)) && ! parent::__get($key) instanceof GeoSpatialObject){
            echo "instantiating object...";
            $this->setAttribute( $key ,  Geo::fromQuery(Geo::bin2text(parent::__get($key))) );
        }
        return parent::__get($key);
    }

}

<?php


namespace LorenzoGiust\GeoLaravel;

use DB;

class Model extends \Illuminate\Database\Eloquent\Model
{

    //TODO: aggiungere anche supporto per 'linestring', 'multipoint', 'multilinestring', 'multipolygon', 'geometrycollection'
    protected static $geotypes = ['points', 'line_strings', 'polygons'];
    
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

                $classname = "LorenzoGiust\\GeoLaravel\\" . ucfirst(str_singular(camel_case($geotype)));
                foreach ($attrnames as $attrname){
                    if( isset($model->$attrname) ){
                        if(! $model->$attrname instanceof $classname)
                            throw new \Exception('Geometry attribute ' . $attrname .' must be an instance of ' . $classname);

                        $model->setAttribute( $attrname ,  DB::raw( $model->$attrname->toRawQuery() ) );
                    }
                }
            }
        });


        static::updating(function($model){

            if( ! isset($model->geometries) ) return;

            foreach($model->geometries as $geotype => $attrnames){
                if( ! in_array($geotype, static::$geotypes ))
                    throw new \Exception('Unknown geotype: ' . $geotype);

                $classname = "LorenzoGiust\\GeoLaravel\\" . ucfirst(str_singular(camel_case($geotype)));
                foreach ($attrnames as $attrname){
                    if( isset($model->$attrname) ){
                        if(! $model->$attrname instanceof $classname)
                            throw new \Exception('Geometry attribute ' . $attrname .' must be an instance of ' . $classname);

                        $model->setAttribute( $attrname ,  DB::raw( $model->$attrname->toRawQuery() ) );
                    }
                }
            }
        });
    }

    public static function hydrate(array $items, $connection = null){
        $ret = parent::hydrate($items, $connection);

        foreach($ret as $item){
            $model = new static;
            if( ! isset( $model->geometries) ) return;
            foreach($model->geometries as $geotype => $attrnames){

                $classname = "LorenzoGiust\\GeoLaravel\\" . ucfirst(str_singular(camel_case($geotype)));
                foreach ($attrnames as $attrname){
                    echo "setting attribute $attrname : $item";
                    if( ! $item->$attrname == ""){
                        $item->setAttribute( $attrname ,  $classname::importFromText(Geo::bin2text($item->$attrname)) );
                    }
                }
            }
        }
        return $ret;
    }
}

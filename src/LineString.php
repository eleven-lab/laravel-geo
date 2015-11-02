<?php


namespace MovEax\GeoSpatial;


/**
 * Class LineString
 * @package App
 */
class LineString extends Geometry implements \Countable
{

    /**
     * @var array
     */
    public $points = [];

    /**
     * @param array Point[]
     */
    public function __construct(array $points){

        array_walk($points, function($p){ if( ! $p instanceof Point) throw new \Exception('A LineString instance must be constructed with Points only.'); });

        if( count($points) < 2 )
            throw new \Exception("A LineString instance must be composed by at least 2 points.");

        $this->points = $points;
    }

    /**
     * Implementazione dell'interfaccia countable
     *
     * @return int
     */
    public function count(){
        return count($this->points);
    }

    /**
     * Controlla se è di tipo circolare, cioè se ha almeno 4 punti di cui il primo e l'ultimo coincidono.
     * Necessario per i Polygon
     *
     * @return bool
     */
    public function circular(){
        return count($this->points) > 3 &&  $this->points[0] == $this->points[ count($this->points) -1 ];
    }

    /**
     * @return string
     */
    public function toQuery(){
        return "LINESTRING" . $this;
    }

    public function __toString(){
        return "(" . implode(', ', $this->points ) . ")";
    }

    /**
     * Importa un polygon con una stringa del tipo "lat lon, lat lon, ...."
     *
     * @param $string
     * @return Polygon
     */
    public static function import($string){

        // TODO: controllo integrità dati in input
        // TODO: prevedere import di più linestring
        $tmp_points = explode(",", $string);
        $points = [];
        foreach($tmp_points as $point){
            $points[] = Point::import($point);
        }
        return new LineString($points);
    }

}




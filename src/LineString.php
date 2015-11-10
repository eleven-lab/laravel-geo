<?php


namespace LorenzoGiust\GeoLaravel;
use PhpParser\Node\Scalar\MagicConst\Line;


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
     * @return LineString
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

    public function lenght(){
        $len_vinc = 0;
        $len_harv = 0;
        for($i = 0; $i < count($this)-2 ; $i++ ){
            //$len_vinc += static::vincentyGreatCircleDistance($this->points[$i], $this->points[$i+1]);
            $len_harv += Geo::distance($this->points[$i], $this->points[$i+1]);
        }
        //return ['harvesine' => $len_harv, 'vincenty' => $len_vinc];
        return $len_harv;

    }


    /*
     * Per ogni step p1-p2 dei punti che compongono la LineString, calcolo d1=d(p,p1) e d2=d(p,p2).
     * Seleziono lo step come candidato all'inserimento se d1+d2 <= d(p1,p2)*soglia_di_tolleranza.
     *
     * TODO: aggiungere condizione più forte oltre a quella con la coppia di punti in osservazione, es. quella con
     *      coppie di punti adiacenti
     * TODO: valutare implementazione di ricerca binaria
     *
     * @param Point $p
     *
     */
     public function insertPoint(Point $p){

         // Se il punto è già presente nella LineString non faccio modifiche
         if(array_search($p, $this->points) !== false){
             return;
         }

        $threshold = 0.02;
        for( $i = 0 ; $i < count($this->points) - 2 ; $i++ ) {

            //echo "valuto $i-esima coppia di punti: " .$this->points[$i]. " " .$this->points[$i+1]. "\n";

            $distance       = static::haversineGreatCircleDistance($this->points[$i], $p) + static::haversineGreatCircleDistance($p, $this->points[$i + 1]);
            $step_distance  = static::haversineGreatCircleDistance($this->points[$i], $this->points[$i + 1]);

            //echo "distanza totale: \t\t $distance\n";
            //echo "step distance: \t\t\t $step_distance\n";

            if( $distance > $step_distance - ($step_distance*$threshold) && $distance < ($step_distance + $step_distance*$threshold) ){
                $newpoints = array_slice( $this->points, 0, $i+1, true );
                $newpoints = array_merge( $newpoints, [$p] );
                $newpoints = array_merge( $newpoints, array_slice($this->points, $i+1, count($this->points) - ($i+1)) );
                $this->points = $newpoints;
                break;

            }
        }
    }


    /**
     * Cerca il punto passato come parametro nella LineString, se:
     * - è il primo o l'ultimo
     * - non è presente
     * ritorna la linestring stessa
     *
     * altrimenti spezza la linestring esattamente nel punto indicato, e ritorna 2 linestring differenti
     *
     * @param $split
     * @return array
     */
    public function splitByPoint(Point $split){

        // se il punto di split è all'inizio o alla fine, ritorna la linestring intera
        reset($this->points);
        if( $this->points[0] == $split)
            return [$split, $this];

        if ($this->points[count($this->points)-1] == $split)
            return [$this, $split];

        $splitted = [];
        $position = array_search($split, $this->points);
        echo "pos:$position  ";

        // se il punto non è presente, ritorno la linestring intera
        if($position === false)
            return [$this, null];

        else{
            array_push( $splitted, new LineString(array_slice($this->points, 0, $position+1)) );
            array_push( $splitted, new LineString(array_slice($this->points, $position, count($this->points) - $position ) ) );
        }
        return $splitted;
    }


    /**
     * @param LineString $l1
     * @param LineString $l2
     * @return array Point
     */
    public static function diff(LineString $l1, LineString $l2){
        $diffs = array_diff($l1->points, $l2->points);
        foreach($diffs as $diff){
            $pos = array_search($diff, $l1->points);
            echo "Il point $diff è presente solo nella prima LineString, in posizione $pos\n";
        }
        return $diffs;
    }
}




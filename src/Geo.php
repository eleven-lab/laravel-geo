<?php
/**
 * Created by PhpStorm.
 * User: lorenzo
 * Date: 16/10/15
 * Time: 15.48
 */

namespace MovEax\GeoSpatial;

use MovEax\GeoSpatial\Point as Point;
use \Carbon\Carbon as Carbon;

class Geo {

    /**
     * @param string $point  -  Punto da controllare nel formato lat lng (separate da spazio).
     * @return array $areas  -  Array contenente la lista di aree che contengono il punto (se presenti).
     */

    public static function getPathInfo(array $points, array $options = []){
        $start_time = round(microtime(true) * 1000); // usato per calcolo durata query
        // Google Api Parameters
        // url: https://developers.google.com/maps/documentation/directions/intro
        $base_url = "https://maps.googleapis.com/maps/api/directions/";
        $params = [];

        $option_list = [
            'output_format' => [
                'default' => 'json',
                'choice'   => ['xml', 'json']
            ],
            'origin' => [
                'default' => reset($points)->toGoogleAPIFormat()
            ],
            'destination' => [
                'default' => end($points)->toGoogleAPIFormat()
            ],
            'key' => [
                'default' => false
            ],
            'mode' => [
                'default' => 'driving',
                'choice' => ['walking', 'bycicling', 'transit']
            ],
            'waypoints' => [            // punti intermedi separati da |
                'default' => false
            ],
            'alternatives' => [
                'default' => false,
                'choice' => ['true', 'false']
            ],
            'avoid' => [
                'default' => false,
                'choice' => ['tolls', 'highways', 'ferries', 'indoor']
            ],
            'units' => [
                'default' => 'metric',
                'choice' => ['metric', 'imperial']
            ],
            'region' => [
                'default' => 'IT'
            ],
            'departure_time' => [       // timestamp unix
                'default' => 'now'
            ],
            'arrival_time' => [         // esclusivo con il precedente
                'default' => false
            ],
            'transit_mode' => [
                'default' => false
            ]
        ];

        foreach( $option_list as $option => $value){
            if( isset($options[$option]) && $options[$option] != "" ){
                if( isset( $value['choice'] ) ){
                    if ( ! in_array($options[$option], $value['choice']) )
                        throw new \Exception("Invalid parameter for option $option: " . $options[$option] );
                }
                $params[$option] = $options[$option];
            }else
            $params[$option] = $value['default'];
        }

        $url = $base_url . $params['output_format'] . "?";
        foreach($params as $key=>$value){
            if($value)
                $url .= '&'.$key.'='.$value;
        }

        echo $url;
        return;

        try{
            $data = file_get_contents($url);
            $data = json_decode($data);
        }catch(Exception $e){
            // TODO: creare eccezione personalizzata e gestirla nell HANDLER
            throw new Exception("Error loading MAPS API");
        }

        $distance = $data->routes[0]->legs[0]->distance->value; // metri
        $duration = $data->routes[0]->legs[0]->duration->value; // secondi
        $polyline = \Polyline::decode($data->routes[0]->overview_polyline->points);

        $points = [];
        while(count($polyline) > 0){
            array_push($points, new Point(array_shift($polyline), array_shift($polyline)));
        }
        $path = new LineString($points);

        $end_time = round(microtime(true) * 1000);

        return ["distance" => $distance, "duration" => $duration, "path" => $path, "query_time" => ($end_time-$start_time)];
    }


    // GEOSPATIAL HELPERS

    public static function bin2text($binary){
        return \DB::select('select AsText(0x'.bin2hex($binary).') as x')[0]->x;
    }



    // GEOMETRY OPERATION

    public static function intersect(Geometry $g1, Geometry $g2){
        $points = [];
        $tmp = [];

        $multipoint = \DB::select("select AsText( ST_Intersection(".$g1->toRawQuery().",".$g2->toRawQuery().") ) as x")[0]->x;

        if (is_null($multipoint)) return [];

        if(strpos($multipoint, "MULTIPOINT(") == 0){
            $tmp = explode(",", substr(substr($multipoint, 11), 0, -1));

        }else if(strpos($multipoint, "POINT(") == 0){
            $tmp = explode(",", substr(substr($multipoint, 6), 0, -1));
        }else{
            // TODO: rimuovere assert, debug only
            assert('ne null, ne multipoint ne point !!!');
        }

        foreach( $tmp as $point){
            $tmp2 = explode(" ", $point);
            array_push($points, new Point($tmp2[0], $tmp2[1]));
        }
        return $points;
    }

    public static function contains(Polygon $polygon, Point $point){
        return (bool)\DB::select("select ST_contains(".$polygon->toRawQuery().",".$point->toRawQuery().") as x")[0]->x;
    }

}


<?php
/**
 * Created by PhpStorm.
 * User: lorenzo
 * Date: 16/10/15
 * Time: 15.48
 */

namespace LorenzoGiust\GeoLaravel;

use LorenzoGiust\GeoLaravel\Point;
use \Carbon\Carbon ;
use LorenzoGiust\GeoLaravel\Exceptions\GoogleDirectionException;
use LorenzoGiust\GeoLaravel\Exceptions\GeoException;

class Geo {

    protected static $greatCircleproviders = [ 'haversine', 'vincenty' ];

    /**
     * @param array $params
     * @return GoogleDirectionResponse
     */

    public static function getGoogleDirections(array $options = []){

        $start_time = round(microtime(true) * 1000); // usato per calcolo durata query
        if( !isset($options['origin']) || !isset($options['destination']) )
            throw new \Exception('A path needs origin and destination to be calculated');

        // Google Api Parameters
        // url: https://developers.google.com/maps/documentation/directions/intro
        $base_url = "https://maps.googleapis.com/maps/api/directions/";
        $params = [];


        // TODO: aggiungere config file per il package con i default
        $option_list = [
            'output_format' => [
                'default' => 'json',
                'choice'   => ['xml', 'json']
            ],
            'origin' => [
                'default' => false
            ],
            'waypoints' => [            // punti intermedi separati da |
                'default' => false
            ],
            'destination' => [
                'default' => false
            ],
            'key' => [
                'default' => false
            ],
            'mode' => [
                'default' => 'driving',
                'choice' => ['walking', 'bycicling', 'transit']
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
            }else{
                if($value['default'] !== false)
                    $params[$option] = $value['default'];
            }
        }

        $url = $base_url . $params['output_format'] . "?" . http_build_query($params) ;
        \Log::debug($url);

        try{

            $data = file_get_contents($url);
            $data = json_decode($data);
        }catch(\Exception $e){
            // TODO: creare eccezione personalizzata e gestirla nell HANDLER
            throw new GoogleDirectionException("Error loading MAPS API - Error:" . $e->getMessage());
        }

        $end_time = round(microtime(true) * 1000);

        return new GoogleDirectionResponse($data, $end_time-$start_time);

    }


    // GEOSPATIAL HELPERS

    public static function bin2text($binary){
        return \DB::select('select AsText(0x'.bin2hex($binary).') as x')[0]->x;
    }



    // GEOMETRY OPERATION
    // TODO: aggiungere tipi di operazioni ed eventuali ritorni

    public static function intersect(Geometry $g1, Geometry $g2)
    {
        $points = [];
        $tmp = [];

        $multipoint = \DB::select("select AsText( ST_Intersection(".$g1->toRawQuery().",".$g2->toRawQuery().") ) as x")[0]->x;


        if (is_null($multipoint)) return [];

        if(strpos($multipoint, "MULTIPOINT(") === 0){
            $tmp = explode(",", substr(substr($multipoint, 11), 0, -1));

        }else if(strpos($multipoint, "POINT(") === 0){
            $tmp = explode(",", substr(substr($multipoint, 6), 0, -1));
        }else{
            // TODO: rimuovere assert, debug only
            assert('ne null, ne multipoint ne point !!!');
        }

        foreach( $tmp as $point ){
            $tmp2 = explode(" ", $point);
            array_push($points, new Point($tmp2[0], $tmp2[1]));
        }
        return $points;
    }

    public static function contains(Polygon $polygon, Point $point)
    {
        return (bool)\DB::select("select ST_contains(".$polygon->toRawQuery().",".$point->toRawQuery().") as x")[0]->x;
    }


    public static function georeverse($address)
    {
        $api_url = "https://maps.google.com/maps/api/geocode/json?language=it&&address=".urlencode($address);
        $response = file_get_contents($api_url);
        if ($json = json_decode($response, true)) {
            return [
                $json['results'][0]['geometry']['location']['lat'],
                $json['results'][0]['geometry']['location']['lng']
            ];
        }
        return null;
    }

    /**
     * @param Polygon $polygon
     * @return \LorenzoGiust\GeoLaravel\Point
     */
    public static function centroid(Polygon $polygon)
    {
        $centroid = \DB::select('select AsText(ST_Centroid('.$polygon->toRawQuery().')) as x')[0]->x;
        return Point::importFromText($centroid);
    }



    /**
     * @param Polygon $polygon
     * @return \LorenzoGiust\GeoLaravel\Polygon
     */
    public static function union( Polygon $p1, Polygon $p2 )
    {
        $union = \DB::select('select AsText(ST_Union('.$p1->toRawQuery().', '.$p2->toRawQuery().')) as x')[0]->x;
        return Polygon::importFromText($union);
    }



    /*
    |--------------------------------------------------------------------------
    | Distance between points
    |--------------------------------------------------------------------------
    |
    | You can get the great circle distance (https://en.wikipedia.org/wiki/Great-circle_distance)
    | between two points  using one of the providers.
    |
    */

    public static function distance(Point $p1, Point $p2, $provider = "haversine"){
        if( ! in_array($provider, self::$greatCircleproviders))
            throw new GeoException('Great circle distance provider not found');

            if( $provider === "haversine" )
                return self::haversineGreatCircleDistance($p1, $p2);
            elseif( $provider === "vicenty" )
                return self::vincentyGreatCircleDistance($p1, $p2);
    }

    private static function vincentyGreatCircleDistance(Point $from, Point $to, $earthRadius = 6371000){
        // convert from degrees to radians
        $latFrom = deg2rad($from->lat);
        $lonFrom = deg2rad($from->lon);
        $latTo = deg2rad($to->lat);
        $lonTo = deg2rad($to->lon);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }

    private static function haversineGreatCircleDistance(Point $from, Point $to, $earthRadius = 6371000){
        // convert from degrees to radians
        $latFrom = deg2rad($from->lat);
        $lonFrom = deg2rad($from->lon);
        $latTo = deg2rad($to->lat);
        $lonTo = deg2rad($to->lon);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }


}


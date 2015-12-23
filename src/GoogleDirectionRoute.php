<?php
/**
 * Created by PhpStorm.
 * User: lorenzo
 * Date: 04/11/15
 * Time: 3.31
 */

namespace LorenzoGiust\GeoLaravel;

use Log;
use LorenzoGiust\GeoLaravel\Exceptions\GeoException;

class GoogleDirectionRoute {

    public $legs = [];
    public $route;
    public $summary;

    public $distance = 0;
    public $duration = 0;

    public function __construct($route){

        //Log::debug('LineString construct -> ' . $route->overview_polyline->points);

        // extract complete route
        $polyline = \Polyline::decode($route->overview_polyline->points);

        //Log::debug('Polyline: ' . json_encode($polyline));

        $points = [];
        while(count($polyline) > 0)
            $points[] = new Point(array_shift($polyline), array_shift($polyline));

        try{
            $this->route = new LineString($points);
        }catch(GeoException $e){
            Log::debug('La rotta ottenuta è composta da un solo punto. Partenza e destinazione coincidono');
            $this->route = $points[0];
        }
        $this->summary = $route->summary;

        // extract legs infos
        $j = 0;
        foreach($route->legs as $leg){

            $this->legs[$j]['distance'] = $leg->distance->value; //mt
            $this->distance += $this->legs[$j]['distance'];

            $this->legs[$j]['duration'] = $leg->duration->value; //s
            $this->duration += $this->legs[$j]['duration'];

            $this->legs[$j]['start'] = new Point($leg->start_location->lat, $leg->start_location->lng);
            $this->legs[$j]['end'] = new Point($leg->end_location->lat, $leg->end_location->lng);

            $j++;
        }
    }

    public function __toString()
    {
        return "Route summary: " . $this->summary . " distance: " . $this->distance . " duration: " . $this->duration;
    }
}
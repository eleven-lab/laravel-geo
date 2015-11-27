<?php
/**
 * Created by PhpStorm.
 * User: lorenzo
 * Date: 04/11/15
 * Time: 3.31
 */

namespace LorenzoGiust\GeoLaravel;


class GoogleDirectionRoute {

    public $legs = [];
    public $route;

    public $distance = 0;
    public $duration = 0;

    public function __construct($route){

        // extract complete route
        $polyline = \Polyline::decode($route->overview_polyline->points);
        $points = [];
        while(count($polyline) > 0){
            array_push($points, new Point(array_shift($polyline), array_shift($polyline)));
        }
        $this->route = new LineString($points);

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
}
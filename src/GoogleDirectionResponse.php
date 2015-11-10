<?php
/**
 * Created by PhpStorm.
 * User: lorenzo
 * Date: 04/11/15
 * Time: 3.31
 */

namespace LorenzoGiust\GeoLaravel;


class GoogleDirectionResponse {

    public $raw_response;
    public $legs = [];

    public $route;
    public $distance = 0;
    public $duration = 0;

    public function __construct($json){

        $this->raw_response = $json;

        // extract complete route
        $polyline = \Polyline::decode($json->routes[0]->overview_polyline->points);
        $points = [];
        while(count($polyline) > 0){
            array_push($points, new Point(array_shift($polyline), array_shift($polyline)));
        }
        $this->route = new LineString($points);

        // extract legs infos
        $i = 0;
        foreach($json->routes[0]->legs as $leg){

            $this->legs[$i]['distance'] = $leg->distance->value; //mt
            $this->distance += $this->legs[$i]['distance'];

            $this->legs[$i]['duration'] = $leg->duration->value; //s
            $this->duration += $this->legs[$i]['duration'];

            $i++;
        }



    }
}
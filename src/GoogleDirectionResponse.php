<?php
/**
 * Created by PhpStorm.
 * User: lorenzo
 * Date: 04/11/15
 * Time: 3.31
 */

namespace LorenzoGiust\GeoLaravel;


use League\Flysystem\Exception;
use \Log;

class GoogleDirectionResponse {

    public $raw_response;

    public $routes = [];

    public function __construct($json){

        $this->raw_response = $json;

        foreach($json->routes as $route){
            $this->routes[] = new GoogleDirectionRoute($route);
        }

    }

    public static function getBestRoute(array $routes){
        Log::debug('Entrato in getBestRoute, trovate ' . count($routes) . ' rotte');
        $treshold = 0.05;

        $merged_routes = [];
        foreach($routes as $route)
            $merged_routes = array_merge($merged_routes, $route);

        Log::debug('Dopo il merge ho ' . count($merged_routes) .' rotte');

        $merged_routes_unique = array_unique($merged_routes);

        Log::debug('Dopo lo unique ho ' . count($merged_routes_unique) .' rotte');

        usort($merged_routes_unique, [get_called_class(), "cmp"]);

        $selected_route = reset($merged_routes_unique);
        Log::debug('Selezionata rotta più veloce come default: ' . $selected_route);

        for( $i = 1; $i < count($merged_routes_unique); $i++){
            $cur = $merged_routes_unique[$i];
            Log::debug('Valuto rotta ' . $cur);

            if( $selected_route->distance > $cur->distance ){
                Log::debug('La rotta current ha una distanza inferiore alla selected');
                Log::debug('convenience value: ' . self::convenience($selected_route, $cur));

                if( self::convenience($selected_route, $cur) < $treshold  ){
                    $selected_route = $cur;
                    Log::debug('Nuova rotta selezionata -> ' .$cur);
                }else{
                    Log::debug('Rotta non aggiornata');
                }

            }else{
                Log::debug('La rotta selected ha una distanza inferiore o uguale a quella current');
            }
            //$selected_route = ( !($selected_route->distance <= $cur->distance) && ($this->convenience($selected_route, $cur) < $treshold ) ) ? $cur : $selected_route;
        }

        return $selected_route;

    }

    private static function cmp(GoogleDirectionRoute $r1, GoogleDirectionRoute $r2){
        if( $r1->duration == $r2->duration)
            return 0;

        return ($r1->duration < $r2->duration) ? -1 : 1;
    }

    /**
     * Confronta due rotte e ritorna il valore del rapporto `secondi_risparmiati`/`metri di differenza`
     *
     * @param GoogleDirectionRoute $r1
     * @param GoogleDirectionRoute $r2
     */
    private static function convenience(GoogleDirectionRoute $r1, GoogleDirectionRoute $r2){
        Log::debug("r2-duration (".$r2->duration.") - r1-duration (".$r1->duration.") = " . ($r2->duration - $r1->duration));
        Log::debug("r1-distance(".$r1->distance.") - r2-distance(".$r2->distance.") = " . ($r1->distance - $r2->distance));
        return ( $r2->duration - $r1->duration ) / ( $r1->distance - $r2->distance );
    }
}

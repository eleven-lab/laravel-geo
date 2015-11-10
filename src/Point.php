<?php


namespace LorenzoGiust\GeoLaravel;


class Point extends Geometry
{
    public $lat;
	public $lon;
	
	public function __construct($lat, $lon, $address = null){
        if( is_string($address)){
            list($lat, $lon) = Geo::georeverse($address);
        }

        if( ! ( is_numeric($lat) && is_numeric($lon)) )
            throw new \Exception('Points must be constructed with numeric latitude/longitude, given: ' .$lat.' '.$lon);

		$this->lat = (float)$lat;
		$this->lon = (float)$lon;
	}
	


	// TODO: implement
	public function getParking(){

		return 0;
	}

    public function toGoogleAPIFormat(){
        return $this->lat.",".$this->lon;
    }


    public function __toString(){
        return  $this->lat . " " . $this->lon;
    }

    public function toQuery(){
        return  "POINT(" . $this . ")";
    }

    /**
     * Crea un oggetto Point da una stringa del tipo "41.123123 12.123123"
     * @param $string
     */
    public static function import($string){
        // TODO: aggiungere controllo integrità dati
        $p = explode(" ", trim($string));
        return new Point($p[0], $p[1]);
    }
    /**
     * Crea un oggetto Point da una stringa del tipo "POINT(41.123123 12.123123)"
     * @param $string
     */
    public static function importFromText($string){
        $tmp = substr(substr($string, 6), 0, -1);
        return self::import($tmp);
    }
}

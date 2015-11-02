<?php
/**
 * Created by PhpStorm.
 * User: lorenzo
 * Date: 20/10/15
 * Time: 1.48
 */


namespace LorenzoGiust\GeoLaravel;


interface GeoInterface
{

    /**
     * Format object for MySql query
     *
     * @return string
     */
    public function toQuery();
}
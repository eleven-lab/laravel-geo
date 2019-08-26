<?php

return [

    /*
     |--------------------------------------------------------------------------
     | PostGIS SRID
     |--------------------------------------------------------------------------
     |
     | Default fallback PostGIS SRID is set to 4326 (EPSG:4326 / WGS 84)
     | You can override the value by setting to valid SRID
     |
     */

    'srid' => 4326,

    /*
     |--------------------------------------------------------------------------
     | PostGIS Column Type
     |--------------------------------------------------------------------------
     |
     | Default PostGIS column type is set to geometry
     | Set geometry option below to false if you want to use geography column instead
     |
     */

    'geometry' => true,

];

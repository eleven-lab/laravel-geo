<?php

namespace ElevenLab\GeoLaravel\Database\Schema;

if(version_compare(app()->version(), "5.2.31") < 0){
    class PostgresBuilder extends \Illuminate\Database\Schema\Builder
    {
        use GeoBuilder;
    }

}else{
    class PostgresBuilder extends \Illuminate\Database\Schema\PostgresBuilder
    {
        use GeoBuilder;
    }
}
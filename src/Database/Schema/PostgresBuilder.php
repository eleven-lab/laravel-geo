<?php

namespace ElevenLab\GeoLaravel\Database\Schema;

if(version_compare(app()->version(), "5.2.31") < 0){
    use Illuminate\Database\Schema\Builder as IlluminatePostgresBuilder;
}else{
    use Illuminate\Database\Schema\PostgresBuilder as IlluminatePostgresBuilder;
}

class PostgresBuilder extends IlluminatePostgresBuilder
{
    use GeoBuilder;
}
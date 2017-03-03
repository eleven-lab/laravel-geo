<?php

namespace ElevenLab\GeoLaravel\Database\Schema;

use Illuminate\Database\Schema\Builder as IlluminateBuilder;

class PostgresBuilder extends IlluminateBuilder
{
    use GeoBuilder;
}
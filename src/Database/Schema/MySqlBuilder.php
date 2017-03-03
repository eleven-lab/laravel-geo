<?php

namespace ElevenLab\GeoLaravel\Database\Schema;

use Illuminate\Database\Schema\MySqlBuilder as IlluminateMySqlBuilder;

class MySqlBuilder extends IlluminateMySqlBuilder
{
    use GeoBuilder;
}
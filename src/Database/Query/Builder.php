<?php

namespace ElevenLab\GeoLaravel\Database\Query;

use ElevenLab\PHPOGC\OGCObject;

class Builder extends \Illuminate\Database\Query\Builder
{


    public function limit(OGCObject $geo1, OGCObject $geo2)
    {
        $property = $this->unions ? 'unionLimit' : 'limit';

        if ($value >= 0) {
            $this->$property = $value;
        }

        return $this;
    }

}
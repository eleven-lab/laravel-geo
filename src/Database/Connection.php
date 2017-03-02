<?php

namespace ElevenLab\GeoLaravel\Database;

use ElevenLab\PHPOGC\OGCObject;
use ElevenLab\GeoLaravel\Schema\Builder;
use Illuminate\Database\Query\Expression;

Trait GeoConnection
{
    /**
     * @return Builder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new Builder($this);
    }

    /**
     * @param OGCObject $geo
     * @return Expression
     */
    public function rawGeo(OGCObject $geo)
    {
        return new Expression($this->geoFromText($geo));
    }
}

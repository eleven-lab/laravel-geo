<?php namespace LorenzoGiust\GeoLaravel\Schema;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as IlluminateMySqlGrammar;

/**
 * Extended version of MySqlGrammar with
 * support of 'set' data type
 */
class MySqlGrammar extends IlluminateMySqlGrammar {

    /**
     * Create the column definition for a geometry type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeGeometry(Fluent $column)
    {
        return $column->geotype;
    }


}
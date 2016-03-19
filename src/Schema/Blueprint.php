<?php namespace LorenzoGiust\GeoLaravel\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;

/**
 * Extended version of Blueprint with
 * support of geo data type
 */
class Blueprint extends IlluminateBlueprint
{
    /**
     * Create a new Geometry column on the table.
     *
     * @param  string   $column
     * @return \Illuminate\Support\Fluent
     */
    public function geometry($column, $type)
    {
        $spatial_types = ['point', 'linestring', 'polygon', 'multipoint', 'multilinestring', 'multipolygon', 'geometrycollection'];

        if ( ! in_array( $type, $spatial_types) )
            throw new \Exception('Unknown geometry type: ' . $type);

        // necessario per non overridare type che è già usato nel namespace chiamante
        $geotype = $type;

        return $this->addColumn('geometry', $column, compact('geotype'));
    }
}
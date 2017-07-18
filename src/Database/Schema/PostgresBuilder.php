<?php

namespace ElevenLab\GeoLaravel\Database\Schema;

class PostgresBuilder extends \Illuminate\Database\Schema\Builder
{

    use GeoBuilder;
    /**
     * Determine if the given table exists.
     *
     * @param  string  $table
     * @return bool
     */
    public function hasTable($table)
    {
        if (is_array($schema = $this->connection->getConfig('schema'))) {
            $schema = head($schema);
        }

        $schema = $schema ? $schema : 'public';

        $table = $this->connection->getTablePrefix().$table;

        return count($this->connection->select(
                $this->grammar->compileTableExists(), [$schema, $table]
            )) > 0;
    }
}
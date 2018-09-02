<?php

namespace Karomap\GeoLaravel\Database\Schema;

use Illuminate\Database\Schema\PostgresBuilder as IlluminatePostgresBuilder;

class PostgresBuilder extends IlluminatePostgresBuilder
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
        list($schema, $table) = $this->parseSchemaAndTable($table);

        $table = $this->connection->getTablePrefix().$table;

        return count($this->connection->select(
            $this->grammar->compileTableExists(), [$schema, $table]
        )) > 0;
    }

    /**
     * Parse the table name and extract the schema and table.
     *
     * @param  string  $table
     * @return array
     */
    protected function parseSchemaAndTable($table)
    {
        $table = explode('.', $table);

        if (is_array($schema = $this->connection->getConfig('schema'))) {
            if (in_array($table[0], $schema)) {
                return [array_shift($table), implode('.', $table)];
            }

            $schema = head($schema);
        }

        return [$schema ?: 'public', implode('.', $table)];
    }
}

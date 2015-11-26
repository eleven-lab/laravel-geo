<?php namespace LorenzoGiust\GeoLaravel\Schema;

class Schema extends \Illuminate\Support\Facades\Facade
{

    /**
     * Get a schema builder instance for a connection.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Schema\Builder
     */
    public static function connection($name)
    {
        $connection = static::$app['db']->connection($name);
        return static::useCustomGrammar($connection);
    }

    /**
     * Get a schema builder.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected static function getFacadeAccessor()
    {
        $connection = static::$app['db']->connection();
        return static::useCustomGrammar($connection);
    }

    /**
     * Boot system by calling our custom Grammar
     *
     * @param  object  $connection \Illuminate\Database\Connection
     * @return \Illuminate\Database\Schema\Builder
     */
    protected static function useCustomGrammar($connection)
    {
        // Only for MySqlGrammar
        if (get_class($connection) === 'Illuminate\Database\MySqlConnection') {
            $MySqlGrammar = $connection->withTablePrefix(new MySqlGrammar);
            $connection->setSchemaGrammar($MySqlGrammar);
        }

        $schema = $connection->getSchemaBuilder();
        $schema->blueprintResolver(function($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $schema;
    }

}
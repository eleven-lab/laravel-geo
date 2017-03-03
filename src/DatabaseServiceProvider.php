<?php

namespace ElevenLab\GeoLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\DatabaseManager;
use ElevenLab\GeoLaravel\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseServiceProvider as IlluminateDatabaseServiceProvider;

/**
 * Class DatabaseServiceProvider
 * @package Phaza\LaravelPostgis
 */
class DatabaseServiceProvider extends IlluminateDatabaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Model::clearBootedModels();

        $this->registerEloquentFactory();

        $this->registerQueueableEntityResolver();

        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->app->singleton('db', function ($app) {
            return new DatabaseManager($app, $app['db.factory']);
        });

        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
        });
    }
}


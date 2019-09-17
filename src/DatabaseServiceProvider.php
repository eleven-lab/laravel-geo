<?php

namespace Karomap\GeoLaravel;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseServiceProvider as IlluminateDatabaseServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Karomap\GeoLaravel\Database\Connectors\ConnectionFactory;

class DatabaseServiceProvider extends IlluminateDatabaseServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $configPath = __DIR__.'/../config/geo.php';

        if (function_exists('config_path')) {
            $publishPath = config_path('geo.php');
        } else {
            $publishPath = base_path('config/geo.php');
        }

        $this->publishes([$configPath => $publishPath], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__.'/../config/geo.php';

        $this->mergeConfigFrom($configPath, 'geo');

        Model::clearBootedModels();

        $this->registerConnectionServices();

        $this->registerEloquentFactory();

        $this->registerQueueableEntityResolver();
    }

    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionServices()
    {
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

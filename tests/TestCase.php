<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected $createdTables = [];

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = new Application(__DIR__ . '/app');
        $app->singleton(Kernel::class, \TestApp\ConsoleKernel::class);
        $app->singleton(ExceptionHandler::class, \TestApp\ExceptionHandler::class);
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

    protected function tearDown(): void
    {
        foreach ($this->createdTables as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }
}

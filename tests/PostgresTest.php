<?php

namespace Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Karomap\GeoLaravel\Database\Schema\Blueprint;
use Karomap\GeoLaravel\DoctrineTypes\GeographyType;
use Karomap\GeoLaravel\DoctrineTypes\GeometryType;

class PostgresTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::setDefaultConnection('pgsql');

        // Create PostGIS extension if not exixts
        DB::statement('create extension if not exists postgis');
    }

    /**
     * Create table
     *
     * @param string $tableName
     * @return void
     */
    private function createTable($tableName)
    {
        Schema::dropIfExists($tableName);
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->point('point', 4326);
            $table->multiPoint('multi_point', 4326);
            $table->lineString('linestring', 4326);
            $table->multiLineString('multi_linestring', 4326);
            $table->polygon('polygon', 4326);
            $table->multiPolygon('multi_polygon', 4326);
            $table->geometry('geometry', 4326);
            $table->geometryCollection('geometry_collection', 4326);
            $table->timestamps();

            $table->spatialIndex('point');
            $table->spatialIndex(['polygon', 'geometry']); // PostgreSQL supports multiple columns index
        });
    }

    /**
     * Create table
     *
     * @group pgsql
     */
    public function testCreateTable()
    {
        $tableName = 'geom_test';
        $this->createTable($tableName);
        $this->assertTrue(Schema::hasTable($tableName));
        $this->createdTables[] = $tableName;

        $geometryColumns = [
            'point',
            'multi_point',
            'linestring',
            'multi_linestring',
            'polygon',
            'multi_polygon',
            'geometry',
            'geometry_collection',
        ];

        foreach ($geometryColumns as $column) {
            $this->assertEquals(Schema::getColumnType($tableName, $column), GeometryType::NAME);
        }
    }

    /**
     * Create table using Geography as spatial column type
     *
     * @group pgsql
     */
    public function testGeographyColumn()
    {
        Config::set('geo.geometry', false);

        $tableName = 'geog_test';
        $this->createTable($tableName);
        $this->assertTrue(Schema::hasTable($tableName));
        $this->createdTables[] = $tableName;

        $geometryColumns = [
            'point',
            'multi_point',
            'linestring',
            'multi_linestring',
            'polygon',
            'multi_polygon',
            'geometry',
            'geometry_collection',
        ];

        foreach ($geometryColumns as $column) {
            $this->assertEquals(Schema::getColumnType($tableName, $column), GeographyType::NAME);
        }

        Config::set('geo.geometry', true);
    }
}

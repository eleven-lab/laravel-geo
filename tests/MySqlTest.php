<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Karomap\GeoLaravel\Database\Schema\Blueprint;
use Karomap\GeoLaravel\DoctrineTypes\GeomCollectionType;
use Karomap\GeoLaravel\DoctrineTypes\GeometryType;
use Karomap\GeoLaravel\DoctrineTypes\LineStringType;
use Karomap\GeoLaravel\DoctrineTypes\MultiLineStringType;
use Karomap\GeoLaravel\DoctrineTypes\MultiPointType;
use Karomap\GeoLaravel\DoctrineTypes\MultiPolygonType;
use Karomap\GeoLaravel\DoctrineTypes\PointType;
use Karomap\GeoLaravel\DoctrineTypes\PolygonType;

class MySqlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::setDefaultConnection('mysql');
    }

    /**
     * Create table
     *
     * @group mysql
     */
    public function testCreateTable()
    {
        $tableName = 'geo_test';

        Schema::dropIfExists($tableName);

        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->point('point', 3857);
            $table->multiPoint('multi_point', 3857);
            $table->lineString('linestring', 3857);
            $table->multiLineString('multi_linestring', 3857);
            $table->polygon('polygon', 3857);
            $table->multiPolygon('multi_polygon', 3857);
            $table->geometry('geometry', 3857);
            $table->geometryCollection('geometry_collection', 3857);
            $table->timestamps();

            $table->spatialIndex('point');
        });

        $this->assertTrue(Schema::hasTable($tableName));

        $this->createdTables[] = $tableName;

        $columnTypesMap = [
            'point' => PointType::NAME,
            'multi_point' => MultiPointType::NAME,
            'linestring' => LineStringType::NAME,
            'multi_linestring' => MultiLineStringType::NAME,
            'polygon' => PolygonType::NAME,
            'multi_polygon' => MultiPolygonType::NAME,
            'geometry' => GeometryType::NAME,
            'geometry_collection' => GeomCollectionType::NAME,
        ];

        foreach ($columnTypesMap as $column => $type) {
            $this->assertEquals(Schema::getColumnType($tableName, $column), $type);
        }
    }
}

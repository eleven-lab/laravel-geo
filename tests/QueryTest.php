<?php

namespace Tests;

use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Karomap\GeoLaravel\Database\Schema\Blueprint;
use Karomap\PHPOGC\DataTypes\Point;

class QueryTest extends TestCase
{
    private $tableName = 'query_test';
    private $srid;
    private $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->srid = config('geo.srid', 4326);
        $this->faker = Faker::create('id_ID');

        DB::setDefaultConnection('pgsql');

        // Create PostGIS extension if not exixts
        DB::statement('create extension if not exists postgis');

        Schema::dropIfExists($this->tableName);

        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('address');
            $table->point('location');
            $table->timestamps();

            $table->spatialIndex('location');
        });

        $this->createdTables[] = $this->tableName;
    }

    private function seedDB($count = 10)
    {
        for ($i = 0; $i < $count; $i++) {
            $address = $this->faker->address;
            $location = new Point($this->faker->latitude, $this->faker->longitude, $this->srid);
            $locationRaw = app('db.connection')->geoFromText($location);
            DB::insert(
                "insert into $this->tableName (address, location) values (?, $locationRaw)",
                [$address]
            );
        }
    }

    /**
     * Insert.
     *
     * @group query
     */
    public function testInsert()
    {
        $address = $this->faker->address;
        $location = new Point($this->faker->latitude, $this->faker->longitude, $this->srid);
        $locationRaw = app('db.connection')->geoFromText($location);
        $ret = DB::insert(
            "insert into $this->tableName (address, location) values (?, $locationRaw)",
            [$address]
        );
        $this->assertTrue($ret);
    }

    /**
     * Select.
     *
     * @group query
     */
    public function testSelect()
    {
        $this->seedDB();
        $rows = DB::select("select * from $this->tableName");
        $this->assertNotEmpty($rows);
        $obj = $rows[0];
        $obj->location = Point::fromWKB($obj->location);
        $this->assertInstanceOf(Point::class, $obj->location);
        $this->assertEquals($obj->location->srid, $this->srid);
    }

    /**
     * Test GeoJSON.
     *
     * @group query
     * @group geojson
     */
    public function testGeoJson()
    {
        $this->seedDB();
        $geoJson = DB::table($this->tableName)->getGeoJson('location', ['address']);
        $this->assertJson($geoJson);

        $geoArray = json_decode($geoJson, true);
        $this->assertArrayHasKey('type', $geoArray);
        $this->assertArrayHasKey('features', $geoArray);
    }

    /**
     * Test GeoJSON with column that doesn't exist.
     *
     * @group query
     * @group geojson
     * @group failed
     */
    public function testGeoJsonFail()
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Undefined index: not_exists');

        $this->seedDB();
        DB::table($this->tableName)->getGeoJson('not_exists');
    }

    /**
     * Test GeoJSON with non geometry column.
     *
     * @group query
     * @group geojson
     * @group failed
     */
    public function testGeoJsonFail2()
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('pack(): Type H: illegal hex digit');

        $this->seedDB();
        DB::table($this->tableName)->getGeoJson('address');
    }
}

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
    private $srid = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->srid = config('geo.srid', 4326);

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

        // $this->createdTables[] = self::TABLE_NAME;
    }

    /**
     * Insert
     *
     * @group query
     */
    public function testInsert()
    {
        $faker = Faker::create('id_ID');
        $address = $faker->address;
        $location = new Point($faker->latitude, $faker->longitude, $this->srid);
        $locationRaw = app('db.connection')->geoFromText($location);
        $ret = DB::insert(
            "insert into $this->tableName (address, location) values (?, $locationRaw)",
            [$address]
        );
        $this->assertTrue($ret);
    }

    /**
     * Select
     *
     * @group query
     */
    public function testSelect()
    {
        $this->testInsert();
        $rows = DB::select("select * from $this->tableName");
        $this->assertNotEmpty($rows);
        $obj = $rows[0];
        $obj->location = Point::fromWKB($obj->location);
        $this->assertInstanceOf(Point::class, $obj->location);
        $this->assertEquals($obj->location->srid, $this->srid);
    }
}

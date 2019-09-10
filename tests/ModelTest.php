<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Karomap\GeoLaravel\Database\Schema\Blueprint;
use Karomap\GeoLaravel\Eloquent\Model;
use Karomap\PHPOGC\DataTypes\LineString;
use Karomap\PHPOGC\DataTypes\Point;
use Karomap\PHPOGC\DataTypes\Polygon;
use Tests\TestCase;

class TestModel extends Model
{
    protected $table = 'model_test';

    protected $guarded = [];

    protected $geometries = [
        'points' => ['point'],
        'linestrings' => ['line'],
        'polygons' => ['polygon'],
    ];
}

class ModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::setDefaultConnection('pgsql');

        // Create PostGIS extension if not exixts
        DB::statement('create extension if not exists postgis');
    }

    private function createTable(bool $fresh = true)
    {
        $tablename = 'model_test';

        if ($fresh) {
            Schema::dropIfExists($tablename);
        }

        if (!Schema::hasTable($tablename)) {
            Schema::create($tablename, function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->point('point', 4326)->nullable();
                $table->lineString('line', 4326)->nullable();
                $table->polygon('polygon', 4326)->nullable();
                $table->timestamps();
            });

            $this->createdTables[] = $tablename;
        }
    }

    /**
     * Test model.
     * @group model
     */
    public function testModel()
    {
        $this->createTable();

        /** @var TestModel $instance */
        $instance = TestModel::create([
            'name' => 'Test',
            'point' => new Point(1, 1),
            'line' => LineString::fromArray([[0, 0], [1, 1]]),
            'polygon' => Polygon::fromArray([[[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]]]),
        ]);

        $this->assertInstanceOf(TestModel::class, $instance);
        $this->assertInstanceOf(Point::class, $instance->point);
        $this->assertInstanceOf(LineString::class, $instance->line);
        $this->assertInstanceOf(Polygon::class, $instance->polygon);

        foreach (['point', 'line', 'polygon'] as $attrname) {
            $this->assertEquals(4326, $instance->getSRID($attrname));
        }
    }
}

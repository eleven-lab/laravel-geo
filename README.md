# Features
- GeoSpatial integration on Laravel 5.2+:
    - Create geospatial columns using Schema and migrations
    - Save and retrieve geospatial attributes using directly OpenGeoConsortium Spatial Objects (this package depends from PHP-OGC)
    - Build spatial query directly with the laravel fluent query builder
    - Supported types: Point, MultiPoint, Linestring, MultiLinestring, Polygon, MultiPolygon, GeometryCollection
- Supported drivers:
    - Postgres: Posgis extension Extensions (geometry types)
    - MySql: Extension for Spatial Data (geography types)

Thanks to https://github.com/njbarrett/laravel-postgis for its original work.

# Installation & Configuration

1) Install using composer

```bash
$ composer require elevenlab/laravel-geo
```

2) Replace under the Service Providers section ('providers' array) in config/app.php this line

```php
Illuminate\Database\DatabaseServiceProvider::class,
```    

with this one:

```php
ElevenLab\GeoLaravel\DatabaseServiceProvider::class
```

3) If you need it, under the Alias section ('aliases' array) in config/app.php add this line:

```php
'GeoModel'      => ElevenLab\GeoLaravel\Model::class,
```

# Quick Documentation

## Create table with spatial references
To add a geospatial field to your migration you can use these methods:
- point, multipoint linestring, multilinestring, polygon, multipolygon, geometrycollection

Example (NB: the schema is over-semplified):
```php
<?php
Schema::create('nations', function (Blueprint $table) {
    $table->increments('id');
    $table->string('name');
    $table->polygon('national_bounds');
    $table->point('capital');
    $table->multipolygon('regions_bounds');
    $table->multipoint('regions_capitals');
    $table->linestring('highway');
});
```
## Add spatial attributes to a Model
In order to handle dinamically geospatial attributes during CRUD operations, you need to:
- substitute the Eloquent Model abstract object with a custom Model
- define which attribute belongs to which geospatial type, defining the `$geometries` attribute (you can find [here](https://github.com/eleven-lab/laravel-geo/blob/master/src/Eloquent/Model.php#L15-L21) the available types)

```php
<?php namespace App;

use ElevenLab\GeoLaravel\Eloquent\Model as GeoModel;

class Country extends GeoModel
{
    protected $table = "countries";

    protected $geometries = [
        "polygons" =>   ['national_bounds'],
        "points" => ['capital'],
        "multipolygons" => ['regions_bounds'],
        "multipoints" => ['regions_capitals'],
        "linestrings" => ['highway']
    ];
}
```

## Manipulate spatial attributes of a Model

```php
<?php
use ElevenLab\GeoLaravel\DataTypes\Point as Point;
use ElevenLab\GeoLaravel\DataTypes\Linestring as Linestring;
use ElevenLab\GeoLaravel\DataTypes\Polygon as Polygon;

$rome = new Point(41.9102415,12.3959149);
$milan = new Point(45.4628328,9.1076927);
$naples = new Point(40.8540943,14.1765626);
$regions_capital = new MultiPoint([$rome, $milan, $naples, ....]);
$italy_bounds = new Polygon([new LineString(getPointArrayOfItalianBounds())]);
$lazio = new LineString(getPointArrayOfLazioBounds());
$campania = new LineString(getPointArrayOfCampaniaBounds());
$lombardia = new LineString(getPointArrayOfLombardiaBounds());
$molise = new LineString(getPointArrayOfMoliseBounds()); # raise MoliseNotFoundException
$regions_bounds = new MultiPolygon([$lazio, $campania, $lombardia, ....]);
$a1 = new LineString(getPointArrayOfA1());

$italy = Country::create([
    'name' => 'Italy',
    'capital' => $rome,
    'national_bounds' => $italy_bounds,
    'regions_bounds' => $regions_bounds,
    'regions_capitals' => $regions_capital,
    'highway' => $a1
]);

$italy = Country::whereName('Italy')->first();
echo get_class($italy->capital); // ElevenLab\PHPOGC\DataTypes\Point
echo get_class($italy->national_bounds); // ElevenLab\PHPOGC\DataTypes\Polygon
echo get_class($italy->regions_bounds); // ElevenLab\PHPOGC\DataTypes\Polygon
echo get_class($italy->regions_capitals); // ElevenLab\PHPOGC\DataTypes\MultiPoint
echo get_class($italy->highway); // ElevenLab\PHPOGC\DataTypes\LineString
```

## Builds queries

There are two different groups of methods that are available, one to use the underlying database engine to perform spatial operations on existing objects, and another to build fluent queries and perform operations on database-resident data.

Given two OGCObjects, you can perform those operations:

- intersection

- difference

- contains

- intersects

- touches

- overlaps

- centroid

- distance

- equals

Given an illuminate Query Builder object, you can use:

- whereEquals

- whereNotEquals

- orWhereEquals

- orWhereNotEquals

- whereContains

- whereNotContains

- orWhereContains

- orWhereNotContains

- whereIntersects

- whereNotIntersects

- orWhereIntersects

- orWhereNotIntersects

- whereTouches

- whereNotTouches

- orWhereTouches

- orWhereNotTouches

- whereOverlaps

- whereNotOverlaps

- orWhereOverlaps

- orWhereNotOverlaps


# ToDo
- improve documentation
    - add examples for "Build queries" section
    - add manual installation guide
- add missing ST_functionsù
- add unit tests

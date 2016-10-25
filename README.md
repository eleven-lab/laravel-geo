# INSTALLATION

Add package to package.json

"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/lorenzogiust/geo-laravel"
    }
],
"require": {
    "lorenzogiust/geo-laravel": "~1.0"
}

(se su bitbucket, usare ->
    require:{
    "elevenlab/laravel-geo":"dev-master"
    }

    "repositories": [
        {
            "type": "vcs",
            "url": "git@bitbucket.org:elevenlab/laravel-geo.git"
        }
    ],


    )

Do not forget to run composer install or composer update rafis/schema-extended after modifying package.json.

Replace Schema "alias" in the configuration file config/app.php and, if needed, insert a reference to GeoModel too:

```
// GeoLaravel integration
'Schema'    => LorenzoGiust\GeoLaravel\Schema\Schema::class,
'GeoModel'  => LorenzoGiust\GeoLaravel\Model::class
```

# GEOSPATIAL

Laravel non offre un supporto diretto ai tipi spaziali, è stato quindi necessario trovare il modo di poterli utilizzare
direttamente sia nei model che nello schema.

## SCHEMA:

E' possibile creare direttamente in fase di migration delle table con una colonna di tipo spatial.
Le tipologie di elemento spaziale che è possibile creare sono: `['point', 'linestring', 'polygon', 'multipoint', 'multilinestring', 'multipolygon', 'geometrycollection']`

es.
```
Schema::create('areas', function (Blueprint $table) {
    $table->increments('id');
    $table->string('name');
    $table->string('ref');

    **$table->geometry('polygon', 'polygon');**
    $table->json('json');
});
```

## MODEL:

Per poter utilizzare le funzioni di Eloquent anche con gli oggetti spatial estendiamo la classe astratta Model. Ogni model
che avrà necessità di utilizzare le funzioni spaziali dovrà estendere la classe GeoModel e dichiarare una variabile
di classe `$geometries`.

### Point
### LineString
### Polygon

-----------------------------------------------------------------------------------------------------------------------
DA FINIRE:
es.
```
public $polygon = [
    ["points" => "", "attribute_name" => "polygon"]
];
```


Memo:
GeomFromText('POINT(11 0)');
GeomFromText('POLYGON((0 0, 10 0, 0 10, 0 0))');

/*

Tinker tests:



namespace App
$p1 = new Point(1,2); $p2 = new Point(3,4); $p3 = new Point(4,5); $p4 = new Point(1, 2);
$poly1 = new Polygon([$p1, $p2, $p3, $p4]); $poly2 = new Polygon([$p1, $p3, $p2, $p4]);
$multipoly = new MultiPolygon([$poly1, $poly2])
$multipoly->toQuery()
=> "(1 2, 3 4, 4 5, 1 2), (1 2, 4 5, 3 4, 1 2)"

namespace App
$p1 = new Point(1,2); $p2 = new Point(3,4); $p3 = new Point(4,5); $p4 = new Point(1, 2);
$poly1 = new Polygon([$p1, $p2, $p3, $p4]); $poly2 = new Polygon([$p1, $p3, $p2, $p4]);
$multipoly = new MultiPolygon([$poly1, $poly2])
$t = new Test()
$t->polygon1 = $poly1
$t->polygon2 = $multipoly
$t->save()

namespace App
$p1 = new Point(1,2); $p2 = new Point(3,4); $p3 = new Point(4,5); $p4 = new Point(1, 2);
$l1 = new LineString([$p1, $p2, $p3, $p4])
$p = new Polygon([$l1, $l1])

 * */
 1) aggiunto in config/app.php l'alias:

         'GeoModel'  => LorenzoGiust\GeoLaravel\Model::class

TODO:
* MultiGeometry - aggiungere classe per gestire oggetti geometrici multipli
* Import da diversi formati usandone uno come normalizzazione
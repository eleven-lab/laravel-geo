<?php
/**
 * Created by PhpStorm.
 * User: lorenzo
 * Date: 20/10/15
 * Time: 11.05
 */

#namespace tests;
use LorenzoGiust\GeoLaravel\Point as Point;
use LorenzoGiust\GeoLaravel\LineString as LineString;
#use LorenzoGiust\GeoLaravel\Geo as Geo;

class TestSpatialGeometry extends \PHPUnit_Framework_TestCase {

    public function testSplitByPoint(){

        $p1 = new Point(1,1);
        $p2 = new Point(1,2);
        $p3 = new Point(1,3);
        $p4 = new Point(1,4);
        $p5 = new Point(1,5);

        $l = new LineString([$p1, $p2, $p3, $p4, $p5]);

        assert( $l->splitByPoint( new Point(0,0) ) == $l );
        assert( $l->splitByPoint( new Point(1,1) ) == $l );
        assert( $l->splitByPoint( new Point(1,5) ) == $l );

        assert( count($l->splitByPoint( new Point(1,2) )) == 2 );

        $splitted = $l->splitByPoint( new Point(1,2) );



    }

    public function testContains(){


    }

    public function testIntersect(){


    }

    public function testPointInsertion(){

    }

}

<?php

//$places = new joshtronic\GooglePlaces('AIzaSyCT6dVNQaPTRsXwDqb1CjoUJncyzGqKDPY');

require_once '../src/GooglePlaces.php';
require_once '../src/GooglePlacesInterface.php';
require_once '../src/GooglePlacesClient.php';

class GooglePlacesTest extends PHPUnit_Framework_TestCase
{
    public function testSetVariable()
    {
        $places      = new joshtronic\GooglePlaces('');
        $places->foo = 'bar';
        $this->assertEquals('bar', $places->foo);
    }

    public function testNearbySearchProximity()
    {
        $client = $this->getMock('GooglePlacesInterface', array('get'));

        $client->expects($this->exactly(1))
               ->method('get')
               ->will($this->returnValue('some return i expect'));

        $places           = new joshtronic\GooglePlaces('', $client);
        $places->location = array(-33.86820, 151.1945860);
        $places->radius   = 800;
        $results          = $places->nearbySearch();
    }

    public function testNearbySearchDistance()
    {

    }

    public function testNearbySearchPagination()
    {

    }

    public function testRadarSearch()
    {

    }

    public function testDetails()
    {

    }
}


<?php

require_once '../src/GooglePlaces.php';
require_once '../src/GooglePlacesInterface.php';
require_once '../src/GooglePlacesClient.php';

class GooglePlacesTest extends PHPUnit_Framework_TestCase
{
    private $places;

    public function setUp()
    {
        $this->places = new joshtronic\GooglePlaces('');
    }

    public function testSetVariable()
    {
        $this->places->foo = 'bar';
        $this->assertEquals('bar', $this->places->foo);
    }

    public function testNearbySearchProximity()
    {
        $client = $this->getMock('GooglePlacesInterface', array('get'));

        $client->expects($this->exactly(1))
               ->method('get')
               ->will($this->returnValue('
                    {
                        "html_attributions" : [],
                        "next_page_token" : "...",
                        "results" : [
                            { },
                            { },
                            { },
                            { },
                            { }
                        ],
                        "status" : "OK"
                    }
                '));

        $this->places->client = $client;
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->radius   = 800;
        $results                = $this->places->nearbySearch();

        $this->assertTrue(is_array($results['results']));
        $this->assertEquals("OK", $results['status']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must specify a location before calling nearbysearch().
     */
    public function testNearbySearchWithoutLocation()
    {
        $results = $this->places->nearbySearch();
    }

    public function testMeters2Lng()
    {
        $this->assertEquals(
            0.0010818843545785356,
            $this->places->meters2lng(100, -33.86820)
        );
    }

    public function testMeters2Lat()
    {
        $this->assertEquals(
            0.0008983120716174308,
            $this->places->meters2lat(100, 151.1945860)
        );
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


<?php

require_once '../src/GooglePlaces.php';
require_once '../src/GooglePlacesClient.php';

class GooglePlacesTest extends PHPUnit_Framework_TestCase
{
    private $places;

    public function setUp()
    {
        $this->places        = new joshtronic\GooglePlaces('');
        $this->places->sleep = 0;
    }

    private function clientSetUp($next = false)
    {
        $client = $this->getMock('GooglePlacesClient', array('get'));

        $client
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('
                {
                    "html_attributions" : [],
                    ' . ($next ? '"next_page_token" : "...",' : '') . '
                    "results"           : [],
                    "status"            : "OK"
                }
            '));

        $this->places->client   = $client;
    }

    public function testSetVariable()
    {
        $this->places->foo = 'bar';
        $this->assertEquals('bar', $this->places->foo);
    }

    public function testNearbySearchProximity()
    {
        $this->clientSetUp(true);
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->radius   = 800;
        $results                = $this->places->nearbySearch();

        $this->assertTrue(is_array($results['results']));
        $this->assertEquals('OK', $results['status']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must specify a location before calling nearbysearch().
     */
    public function testNearbySearchWithoutLocation()
    {
        $this->places->nearbySearch();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must specify a radius.
     */
    public function testNearbySearchWithoutRadius()
    {
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->nearbySearch();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid output, please specify either "json" or "xml".
     */
    public function testNearbySearchInvalidOutput()
    {
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->radius   = 800;
        $this->places->output   = 'foo';
        $this->places->nearbySearch();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid rank by value, please specify either "prominence" or "distance".
     */
    public function testNearbySearchInvalidRankBy()
    {
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->radius   = 800;
        $this->places->rankby   = 'foo';
        $this->places->nearbySearch();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You much specify at least one of the following: "keyword", "name", "types".
     */
    public function testNearbySearchMissingParameters()
    {
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->rankby   = 'distance';
        $this->places->nearbySearch();
    }

    public function testNearbySearchUnsetRadius()
    {
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->rankby   = 'distance';
        $this->places->keyword  = 'cafe';
        $this->places->radius   = 800;
        $results                = $this->places->nearbySearch();

        $this->assertFalse(isset($this->places->radius));
    }

    public function testNearbySearchDistance()
    {
        $client = $this->getMock('GooglePlacesClient', array('get'));

        $client
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('
                {
                    "html_attributions" : [],
                    "next_page_token"   : "...",
                    "results"           : [],
                    "status"            : "OK"
                }
            '));

        $client
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('
                {
                    "html_attributions" : [],
                    "results"           : [],
                    "status"            : "OK"
                }
            '));

        $this->places->client   = $client;
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->rankby   = 'distance';
        $this->places->types    = array('restaurant', 'business');
        $results                = $this->places->nearbySearch();

        $this->assertTrue(is_array($results['results']));
        $this->assertEquals('OK', $results['status']);
    }

    public function testSetPageToken()
    {
        $client = $this->getMock('GooglePlacesClient', array('get'));

        $client
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('
                {
                    "html_attributions" : [],
                    "next_page_token"   : "...",
                    "results"           : [],
                    "status"            : "OK"
                }
            '));

        $client
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('
                {
                    "html_attributions" : [],
                    "results"           : [],
                    "status"            : "OK"
                }
            '));

        $this->places->client    = $client;
        $this->places->location  = array(-33.86820, 151.1945860);
        $this->places->radius    = 100;
        $this->places->pagetoken = '...';
        $results                 = $this->places->nearbySearch();

        $this->assertTrue(is_array($results['results']));
        $this->assertEquals('OK', $results['status']);
    }

    public function testRadarSearch()
    {
        $this->clientSetUp();
        $this->places->location  = array(-33.86820, 151.1945860);
        $this->places->radius    = 100;
        $this->places->keyword   = 'restaurant';
        $results                 = $this->places->radarSearch();

        $this->assertTrue(is_array($results['results']));
        $this->assertEquals('OK', $results['status']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must specify a location before calling radarsearch().
     */
    public function testRadarSearchWithoutLocation()
    {
        $this->places->radarSearch();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must specify a radius.
     */
    public function testRadarSearchWithoutRadius()
    {
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->radarSearch();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You much specify at least one of the following: "keyword", "name", "types".
     */
    public function testRadarSearchMissingParameters()
    {
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->radius   = 100;
        $this->places->radarSearch();
    }

    public function testDetails()
    {
        $this->clientSetUp();
        $this->places->placeid = '123';
        $this->places->rankby  = 'distance';
        $results               = $this->places->details();

        $this->assertTrue(is_array($results['results']));
        $this->assertEquals('OK', $results['status']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must specify either a "placeid" or a "reference" (but not both) before calling details().
     */
    public function testDetailsMissingParameters()
    {
        $this->places->details();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage The returned JSON was malformed or nonexistent.
     */
    public function testInvalidJSON()
    {
        $client = $this->getMock('GooglePlacesClient', array('get'));

        $client
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('[{ foo: "ba"r,, }];'));

        $this->places->client   = $client;
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->radius   = 100;
        $this->places->nearbysearch();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage XML is terrible, don't use it, ever.
     */
    public function testOutputXML()
    {
        $this->places->location = array(-33.86820, 151.1945860);
        $this->places->radius   = 800;
        $this->places->output   = 'xml';
        $this->places->nearbySearch();
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

    public function testNearbySearchProximitySubradius()
    {
        $client = $this->getMock('GooglePlacesClient', array('get'));

        for ($i = 0; $i < 18; $i++)
        {
            $client
                ->expects($this->at($i))
                ->method('get')
                ->will($this->returnValue('
                    {
                        "html_attributions" : [],
                        ' . ($i % 2 ? '' : '"next_page_token"   : "...",') . '
                        "results"           : [{}, {}],
                        "status"            : "OK"
                    }
                '));
        }

        $this->places->client    = $client;
        $this->places->location  = array(-33.86820, 151.1945860);
        $this->places->radius    = 400;
        $this->places->subradius = 200;
        $results                 = $this->places->nearbySearch();

        $this->assertTrue(is_array($results['results']));
        $this->assertEquals(36, count($results['results']));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Subradius should be at least 200 meters
     */
    public function testNearbySearchProximitySubradiusBelow200()
    {
        $this->places->location  = array(-33.86820, 151.1945860);
        $this->places->radius    = 2000;
        $this->places->subradius = 100;
        $results                 = $this->places->nearbySearch();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Subradius should divide evenly into radius.
     */
    public function testNearbySearchProximitySubradiusDivisionError()
    {
        $this->places->location  = array(-33.86820, 151.1945860);
        $this->places->radius    = 2000;
        $this->places->subradius = 233;
        $results                 = $this->places->nearbySearch();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage CURL Error: Protocol yatp not supported or disabled in libcurl
     */
    public function testClientError()
    {
        $client = new joshtronic\GooglePlacesClient();
        $client->get('yatp://foo@bar');
    }
}


<?php

namespace joshtronic;

class GooglePlaces
{
    public  $client    = '';
    public  $sleep     = 3;
    private $key       = '';
    private $base_url  = 'https://maps.googleapis.com/maps/api/place';
    private $method    = null;
    private $response  = null;

    public  $keyword   = null;
    public  $language  = 'en';
    public  $location  = null;
    public  $output    = 'json';
    public  $name      = null;
    public  $pagetoken = null;
    public  $radius    = null;
    public  $rankby    = 'prominence';
    public  $sensor    = false;
    public  $types     = null;
    public  $placeid   = null;
    public  $reference = null;
    public  $opennow   = null;

    public  $subradius = null;
    public  $getmax    = true;
    private $grid      = null;

    private $exceptions = array(
        'base_url', 'client', 'exceptions', 'getmax', 'grid', 'method',
        'output', 'pagetoken', 'response', 'sleep', 'subradius',
    );

    public function __construct($key, $client = false)
    {
        $this->key    = $key;
        $this->client = $client ? $client : new GooglePlacesClient();
    }

    function __set($variable, $value)
    {
        // Compensates for mixed variable naming
        $variable        = str_replace('_', '', strtolower($variable));
        $this->$variable = $value;
    }

    public function __call($method, $arguments)
    {
        $this->output = strtolower($this->output);

        if (!in_array($this->output, array('json', 'xml')))
        {
            throw new \Exception('Invalid output, please specify either "json" or "xml".');
        }

        $method     = $this->method = strtolower($method);
        $url        = implode('/', array($this->base_url, $method, $this->output));
        $parameters = array();
        $parameters = $this->parameterBuilder($parameters);
        $parameters = $this->methodChecker($parameters, $method);

        if (!empty($this->subradius))
        {
            return $this->subdivide($url, $parameters);
        }

        if ($this->pagetoken !== null)
        {
            $parameters['pagetoken'] = $this->pagetoken;
        }

        return $this->queryGoogle($url, $parameters);
    }

    /**
     * Loops through all of our variables to make a parameter list
     */
    private function parameterBuilder($parameters)
    {
        foreach (get_object_vars($this) as $variable => $value)
        {
            // Except these variables
            if (!in_array($variable, $this->exceptions))
            {
                // Assuming it's not null
                if ($value !== null)
                {
                    // Converts boolean to string
                    if (is_bool($value))
                    {
                        $value = $value ? 'true' : 'false';
                    }

                    switch ($variable)
                    {
                        // Allows LatLng to be passed as an array
                        case 'location':
                            if (is_array($value))
                            {
                                // Just in case it's an associative array
                                $value = array_values($value);
                                $value = $value[0] . ',' . $value[1];
                            }
                            break;

                        // Checks that it's a value rank by value
                        case 'rankby':
                            $value = strtolower($value);

                            if (!in_array($value, array('prominence', 'distance')))
                            {
                                throw new \Exception('Invalid rank by value, please specify either "prominence" or "distance".');
                            }
                            break;

                        // Allows types to be passed as an array
                        case 'types':
                            if (is_array($value))
                            {
                                $value = implode('|', $value);
                            }
                            break;
                    }

                    $parameters[$variable] = $value;
                }
            }
        }
        return $parameters;
    }

    /**
     * takes the parameters and method to throw exceptions or modify parameters as needed
     * @todo Method to sanity check passed types
     */
    private function methodChecker($parameters, $method)
    {
        if (!isset($parameters['pagetoken']))
        {
            switch ($method)
            {
                case 'nearbysearch':
                    if (!isset($parameters['location']))
                    {
                        throw new \Exception('You must specify a location before calling nearbysearch().');
                    }
                    elseif (isset($parameters['rankby']))
                    {
                        switch ($parameters['rankby'])
                        {
                            case 'distance':
                                if (!isset($parameters['keyword'])
                                    && !isset($parameters['name'])
                                    && !isset($parameters['types']))
                                {
                                    throw new \Exception('You much specify at least one of the following: "keyword", "name", "types".');
                                }

                                if (isset($parameters['radius']))
                                {
                                    unset($this->radius, $parameters['radius']);
                                }
                                break;

                            case 'prominence':
                                if (!isset($parameters['radius']))
                                {
                                    throw new \Exception('You must specify a radius.');
                                }
                                break;
                        }
                    }

                    break;

                case 'radarsearch':
                    if (!isset($parameters['location']))
                    {
                        throw new \Exception('You must specify a location before calling radarsearch().');
                    }
                    elseif (!isset($parameters['radius']))
                    {
                        throw new \Exception('You must specify a radius.');
                    }
                    elseif (empty($parameters['keyword'])
                        && empty($parameters['name'])
                        && empty($parameters['types']))
                    {
                        throw new \Exception('You much specify at least one of the following: "keyword", "name", "types".');
                    }

                    if (isset($parameters['rankby']))
                    {
                        unset($this->rankby, $parameters['rankby']);
                    }

                    break;

                case 'details':
                    if (!(isset($parameters['reference'])
                        ^ isset($parameters['placeid'])))
                    {
                        throw new \Exception('You must specify either a "placeid" or a "reference" (but not both) before calling details().');
                    }

                    if (isset($parameters['rankby']))
                    {
                        unset($this->rankby, $parameters['rankby']);
                    }

                    break;
            }
        }

        return $parameters;
    }


    /**
     * Submits request via curl, sets the response, then returns the response
     */
    private function queryGoogle($url, $parameters)
    {
        if ($this->pagetoken !== null)
        {
            $parameters['pagetoken'] = $this->pagetoken;
            sleep($this->sleep);
        }

        // Couldn't seem to get http_build_query() to work right so...
        $querystring = '';

        foreach ($parameters as $variable => $value)
        {
            if ($querystring != '')
            {
                $querystring .= '&';
            }

            $querystring .= $variable . '=' . $value;
        }

        $response = $this->client->get($url . '?' . $querystring);

        if ($this->output == 'json')
        {
            $response = json_decode($response, true);

            if ($response === null)
            {
                throw new \Exception('The returned JSON was malformed or nonexistent.');
            }
        }
        else
        {
            throw new \Exception('XML is terrible, don\'t use it, ever.');
        }

        $this->response = $response;

        return $this->response;
    }

    /**
     * Returns the longitude equal to a given distance (meters) at a given latitude
     */
    public function meters2lng($meters, $latitude)
    {
        return $meters / (cos(deg2rad($latitude)) * 40075160 / 360);
    }

    /**
     * Returns the latitude equal to a given distance (meters)
     */
    public function meters2lat($meters)
    {
        return $meters / (40075160 / 360);
    }

    /**
     * Returns the aggregated responses for a subdivided search
     */
    private function subdivide($url, $parameters)
    {
        if ($this->subradius < 200)
        {
            throw new \Exception('Subradius should be at least 200 meters.');
        }

        $quotient = $parameters['radius'] / $this->subradius;

        if ($parameters['radius'] % $this->subradius || $quotient % 2)
        {
            throw new \Exception('Subradius should divide evenly into radius.');
        }

        $center    = explode(',', $parameters['location']);
        $centerlat = $center[0];
        $centerlng = $center[1];
        $count     = $quotient;
        $lati      = $this->meters2lat($this->subradius * 2);

        $this->grid['results'] = array();

        for ($i = $count / 2 * -1; $i <= $count / 2; $i++)
        {
            $lat  = $centerlat + $i * $lati;
            $lngi = $this->meters2lng($this->subradius * 2, $lat);

            for ($j = $count / 2 * -1; $j <= $count / 2; $j++)
            {
                $lng = $centerlng + $j * $lngi;
                $loc = $lat . ',' . $lng;

                $parameters['location'] = $loc;
                $parameters['radius']   = $this->subradius;

                $pagetoken = true;

                while ($pagetoken)
                {
                    $this->queryGoogle($url, $parameters);

                    $this->grid[$i][$j] = $this->response;

                    $this->grid['results'] = array_merge(
                        $this->grid['results'],
                        $this->response['results']
                    );

                    if (isset($this->response['next_page_token']))
                    {
                        $this->pagetoken = $this->response['next_page_token'];
                    }
                    else
                    {
                        $this->pagetoken = null;
                        $pagetoken       = false;
                    }
                }
            }
        }

        return $this->grid;
    }
}


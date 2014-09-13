<?php

namespace joshtronic;

class GooglePlaces
{
    private $key      = '';
    private $base_url = 'https://maps.googleapis.com/maps/api/place';
    private $method   = null;
    private $response = null;

    public $keyword   = null;
    public $language  = 'en';
    public $location  = null;
    public $output    = 'json';
    public $name      = null;
    public $pagetoken = null;
    public $radius    = null;
    public $rankby    = 'prominence';
    public $sensor    = false;
    public $types     = null;
    public $placeid   = null;
    public $reference = null;
    public $opennow   = null;

    public $subradius = null;
    public $getmax    = true;
    private $grid     = null;

    public function __construct($key)
    {
        $this->key = $key;
    }

    function __set($variable, $value)
    {
        // Compensates for mixed variable naming
        $variable        = str_replace('_', '', strtolower($variable));
        $this->$variable = $value;
    }

    public function __call($method, $arguments)
    {
        $method     = $this->method = strtolower($method);
        $url        = implode('/', array($this->base_url, $method, $this->output));
        $parameters = array();

        $parameters = $this->parameterBuilder($parameters);
        $parameters = $this->methodChecker($parameters, $method);

        if (!empty($this->subradius)) {
            return $this->subdivide($url, $parameters);
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
            if (!in_array($variable, array('base_url', 'method', 'output', 'pagetoken', 'response', 'subradius', 'getmax','grid')))
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

                        // Checks that the output is value
                        case 'output':
                            $value = strtolower($value);

                            if (!in_array($value, array('json', 'xml')))
                            {
                                throw new \Exception('Invalid output, please specify either "json" or "xml".');
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
                                if (!isset($parameters['keyword']) && !isset($parameters['name']) && !isset($parameters['types']))
                                {
                                    throw new \Exception('You much specify at least one of the following: keyword, name, types.');
                                }

                                if (isset($parameters['radius']))
                                {
                                    unset($parameters['radius']);
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
                        throw new \Exception('You must specify a location before calling nearbysearch().');
                    }
                    elseif (!isset($parameters['radius']))
                    {
                        throw new \Exception('You must specify a radius.');
                    }
                    elseif (empty($parameters['keyword']) && empty($parameters['name']) && empty($parameters['types']))
                    {
                        throw new \Exception('A Radar Search request must include at least one of keyword, name, or types.');
                    }

                    if (isset($parameters['rankby']))
                    {
                        unset($parameters['rankby']);
                    }

                    break;

                case 'details':
                    if (!(isset($parameters['reference']) ^ isset($parameters['placeid'])))
                    {
                        throw new \Exception('You must specify either a placeid or a reference (but not both) before calling details().');
                    }

                    if (isset($parameters['rankby']))
                    {
                        unset($parameters['rankby']);
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
            sleep(3);
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

        $curl = curl_init();

        $options = array(
            CURLOPT_URL            => $url . '?' . $querystring,
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_RETURNTRANSFER => true,
        );

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        if ($error = curl_error($curl))
        {
            throw new \Exception('CURL Error: ' . $error);
        }

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

        curl_close($curl);

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
        if (($this->radius % $this->subradius) || ($this->subradius < 200) || (($this->radius / $this->subradius) % 2))
        {
            throw new \Exception('Subradius should divide evenly into radius. Also, subradius should be 200 meters or so. (ex: 2000/200 = 10x10 grid. NOT 2000/33 = 60.6x60.6 grid. NOT 2000/16 = 125x125 grid)');
        }

        $center    = explode(',', $this->location);
        $centerlat = $center[0];
        $centerlng = $center[1];
        $count     = $this->radius / $this->subradius;
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

                $this->queryGoogle($url, $parameters);

                $this->grid[$i][$j]    = $this->response;
                $this->grid['results'] = array_merge($this->grid['results'], $this->response['results']);

                while ($this->response['next_page_token'])
                {
                    $this->pagetoken = $this->response['next_page_token'];

                    $this->queryGoogle($url, $parameters);

                    $this->grid[$i][$j]    = array_merge($this->grid[$i][$j], $this->response);
                    $this->grid['results'] = array_merge($this->grid['results'], $this->response['results']);
                    $this->pagetoken       = null;
                }
            }
        }

        return $this->grid;
    }
}


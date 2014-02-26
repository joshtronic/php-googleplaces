<?php

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
	public $reference = null;
	public $opennow   = null;

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

		// Loops through all of our variables to make a parameter list
		foreach (get_object_vars($this) as $variable => $value)
		{
			// Except these variables
			if (!in_array($variable, array('base_url', 'method', 'output', 'pagetoken', 'response')))
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
								throw new Exception('Invalid output, please specify either "json" or "xml".');
							}
							break;

						// Checks that it's a value rank by value
						case 'rankby':
							$value = strtolower($value);

							if (!in_array($value, array('prominence', 'distance')))
							{
								throw new Exception('Invalid rank by value, please specify either "prominence" or "distance".');
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

		if (!isset($parameters['pagetoken']))
		{
			switch ($method)
			{
				case 'nearbysearch':
					if (!isset($parameters['location']))
					{
						throw new Exception('You must specify a location before calling nearbysearch().');
					}
					elseif (isset($parameters['rankby']))
					{
						switch ($parameters['rankby'])
						{
							case 'distance':
								if (!isset($parameters['keyword']) && !isset($parameters['name']) && !isset($parameters['types']))
								{
									throw new Exception('You much specify at least one of the following: keyword, name, types.');
								}

								if (isset($parameters['radius']))
								{
									unset($parameters['radius']);
								}

								break;

							case 'prominence':
								if (!isset($parameters['radius']))
								{
									throw new Exception('You must specify a radius.');
								}

								break;
						}
					}

					break;

				case 'radarsearch':
					if (!isset($parameters['location']))
					{
						throw new Exception('You must specify a location before calling nearbysearch().');
					}
					elseif (!isset($parameters['radius']))
					{
						throw new Exception('You must specify a radius.');
					}
					elseif (empty($parameters['keyword']) && empty($parameters['name']) && empty($parameters['types']))
					{
						throw new Exception('A Radar Search request must include at least one of keyword, name, or types.');
					}

					if (isset($parameters['rankby']))
					{
						unset($parameters['rankby']);
					}

					break;

				case 'details':
					if (!isset($parameters['reference']))
					{
						throw new Exception('You must specify a reference before calling details().');
					}

					if (isset($parameters['rankby']))
					{
						unset($parameters['rankby']);
					}

					break;
			}
		}

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
			throw new Exception('CURL Error: ' . $error);
		}

		if ($this->output == 'json')
		{
			$response = json_decode($response, true);

			if ($response === null)
			{
				throw new Exception('The returned JSON was malformed or nonexistent.');
			}
		}
		else
		{
			throw new Exception('XML is terrible, don\'t use it, ever.');
		}

		curl_close($curl);

		$this->response = $response;

		return $this->response;
	}

	// @todo Method to sanity check passed types
}

?>

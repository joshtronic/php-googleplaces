<?php

class GooglePlaces
{
	private $key      = '';
	private $base_url = 'https://maps.googleapis.com/maps/api/place';

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
		$method = strtolower($method);
		$url    = implode('/', array($this->base_url, $method, $this->output));

		$parameters = array();

		// Loops through all of our variables to make a parameter list
		foreach (get_object_vars($this) as $variable => $value)
		{
			// Except these variables
			if (!in_array($variable, array('base_url', 'output')))
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

		switch ($method)
		{
			case 'nearbysearch':
				if (!isset($parameters['location']))
				{
					throw new Exception('You must specify a location before calling nearbysearch.');
				}
				elseif (isset($parameters['rankby']))
				{
					if ($parameters['rankby'] == 'distance')
					{
						if (!isset($parameters['keyword']) && !isset($parameters['name']) && !isset($parameters['types']))
						{
							throw new Exception('You much specify at least one of the following: keyword, name, types.');
						}

						if (isset($parameters['radius']))
						{
							unset($parameters['radius']);
						}
					}
				}

				break;
		}

		$querystring = '';

		foreach ($parameters as $variable => $value)
		{
			if ($querystring != '')
			{
				$querystring .= '&';
			}

			$querystring .= $variable . '=' . $value;
		}

		echo $url . '?' . $querystring;

		$curl = curl_init();

		$options = array(
			CURLOPT_URL            => $url . '?' . $querystring,
			CURLOPT_HEADER         => false,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_RETURNTRANSFER => true,
		);

		curl_setopt_array($curl, $options);

		$response = curl_exec($curl);

		var_dump($response, curl_error($curl));
		curl_close($curl);

		exit;
	}

	// @todo Method to sanity check passed types
}

?>

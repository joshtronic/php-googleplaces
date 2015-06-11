<?php

namespace joshtronic;

class GooglePlacesClient
{
    public function get($url)
    {
        $curl = curl_init();

        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_RETURNTRANSFER => true,
        );

        curl_setopt_array($curl, $options);

        // Add certificate for Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        }

        $response = curl_exec($curl);

        if ($error = curl_error($curl))
        {
            throw new \Exception('CURL Error: ' . $error);
        }

        curl_close($curl);

        return $response;
    }
}


<?php

namespace joshtronic;

class GooglePlacesClient
{
    public function get($url)
    {
        $curl = curl_init();

        $ssl_verifypeer = true;
        // Remove ssl certificate verification for Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
        {
            $ssl_verifypeer = false;
        }

        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => $ssl_verifypeer,
            CURLOPT_RETURNTRANSFER => true,
        );

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        if ($error = curl_error($curl))
        {
            throw new \Exception('CURL Error: ' . $error);
        }

        curl_close($curl);

        return $response;
    }
}


<?php

namespace App\Services;

class CommunicateToTauri
{
    protected $baseurl = 'http://chapi.tauri.hu/apiIndex.php';

    protected $apikey;

    protected $secret;

    /**
     * Construct the class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->apikey = config('services.tauri.key');
        $this->secret = config('services.tauri.secret');
    }

    /**
     * Basic CURL-based communication with the Armory Server.
     * The request is json and urlencoded.
     * The request is url and json decoded.
     * Stops execution on error.
     */
    public function communicate($request)
    {
        $request['secret'] = $this->secret;

        $ch = curl_init($this->baseurl . '?apikey=' . $this->apikey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Armory Public API client');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        // curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode(json_encode($request)));
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            print $err;
            exit;
        } else {
            $ret = json_decode(urldecode($response), true);
            if (json_last_error() != JSON_ERROR_NONE) {
                print 'JSON Error: ' . json_last_error();
                print $response;
                exit;
            } else {
                return $ret;
            }
        }
    }
}

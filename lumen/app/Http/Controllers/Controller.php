<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use GuzzleHttp\Client as ClientHttp;

class Controller extends BaseController
{
    /**
     * @OA\Info(
     *   title="Mario Baggio test",
     *   version="1.0",
     *   @OA\Contact(
     *     email="mario.baggio@gmail.com",
     *     name="Mario Baggio"
     *   )
     * )
     */
    
    public static function sanatizeStringInput($table, $key_name, $value, &$error = null, $options = []) {
        $value = (urldecode($value) == '{'.$key_name.'}') ? null : trim(urldecode($value));
        
        $item = -1;
        
        if (is_null($error)) {
            if (is_null($value)) {
                $error = response()->json(['error' => ''.ucwords($key_name).' can not be empty', 'data' => $value], 412, []);        
            } elseif (empty($options) || !isset($options['avoid_table_check'])) {
                // search for that name - similar category check
                $item = \DB::table($table)->where($key_name, $value)->first();

                if (!empty($item)) {
                    $error = response()->json(['error' => ''.ucwords($key_name).' already exists', 'data' => ['table' => $table, 'object' => $item]], 412, []);        
                }
            }
        }
        
        return $value;
    }
    
    public static function sanatizeIntegerInput($table, $key_name, $value, &$error = null, $options = []) {
        $value = (urldecode($value) == '{'.$key_name.'}') ? null : trim(urldecode($value));
        
        if (is_null($error)) {
            
            $value = (urldecode($value) == '{'.$key_name.'}') ? null : trim($value);
            if (is_null($value)) {
                $error = response()->json(['error' => ''.$key_name.' can not be empty', 'data' => $value], 412, []);        
            } 
            if (is_null($error) && !is_numeric($value)) {
                // check format
                $error = response()->json(['error' => 'Invalid '.$key_name.' value', 'data' => $value], 412, []);
            } 
            if (is_null($error)) {
                // check existant category
                $item = \DB::table($table)->where($key_name, $value)->first();
                if (empty($item) && isset($options['should_exist'])) {
                    $error = response()->json(['error' => 'Invalid '.$table.'.'.$key_name.' - it doesn\'t exist!', 'data' => $value], 412, []);
                } elseif (!empty($item) && isset($options['should_not_exist'])) {
                    $error = response()->json(['error' => 'Invalid '.$table.'.'.$key_name.' - it exist but should not!', 'data' => ['table' => $table, 'object' => $item]], 412, []);
                }
            }

        }
        
        return $value;
    }
    
    public static function sendToElastic($type, $tag, $request)
    {
        //check if defined configuration for elasticsearch
        $elastic_server  = config('elastic.elastic_url') . ':' . config('elastic.elastic_port');
        if (isset($elastic_server)) {
            //get configuration from env
            $env = config('app.env');
            //get elastic timezone
            $elastic_tz  = config('elastic.elastic_timezone');

            //create DateTime Object
            $date_time = new \DateTime();
            //get date from object
            $date = $date_time->format('Y-m-d');
            //transform to elastic date format
            $date_time = str_replace(" ", "T", $date_time->format('Y-m-d H:i:s'));
            //Add timezone
            $date_time .= $elastic_tz;


            //create access token
            $base = config('elastic.elastic_user') . ':' . config('elastic.elastic_pass');
            $token = 'Basic ' . Base64_encode($base);

            //create url for index
            $elasticURL = $elastic_server . '/merchants-' . $env . '-log-' . $date . '/log/' . time();
            $headers = [
                'Content-type' => 'application/json',
                'Accept' => 'application/json',
                'authorization' => $token,
            ];
            //Disable SSL verification for elastic server
            $client = new ClientHttp(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false,),));

            $payload = [
                'date' => $date_time,
                'type' => $type,
                'log' => $tag,
                'tk' => $request
            ];

            try {
                $response = $client->post($elasticURL, [
                    'headers' => $headers,
                    RequestOptions::JSON => $payload
                ]);
            } catch (\Exception $e) {
                //log error

                if (!Cache::has('Notification-sent')) {
                    //$this->LogToSlack('{' . config('app.env') . '} Elasticsearch connection error ' . $e->getMessage());
                    Cache::put('Notification-sent', 'sent', 10);
                }
            }
        }
        
    }
}

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
        $value = trim(urldecode($value));
        $value = ($value == '{'.$key_name.'}') ? null : $value;
        
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
        $value = trim(urldecode($value));
        $value = (empty($value) && isset($options['allow_null'])) ? null : $value;
        $value = (substr($value, 0, 1) == '{' && substr($value, -1, 1) == '}') ? null : $value;
        
        if (is_null($error)) {
            
            if (!is_null($value) || (is_null($value) && !isset($options['allow_null']))) {
                if (is_null($value)) {
                    $error = response()->json(['error' => ''.$key_name.' can not be empty', 'data' => $value], 412, []);        
                } 

                $not_numeric = !is_numeric($value);
                $numeric_but_invalid = is_numeric($value) && isset($options['invalid_value']) && $options['invalid_value'] == $value;
                if (is_null($error) && ($not_numeric || $numeric_but_invalid)) {
                    // check format
                    $error = response()->json(['error' => 'Invalid '.$table.'.'.$key_name.' value (or not accepted)', 'data' => $value], 412, []);
                } else {
                    // cast as a integer
                    $value = intval($value);
                }
                
                if (is_null($error) && $value < 0) {
                    $error = response()->json(['error' => 'Invalid '.$key_name.' value - should be positive', 'data' => [$value]], 412, []);
                }

                if (is_null($error) && !isset($options['avoid_table_check'])) {
                    // check existant category
                    $item = \DB::table($table)->where($key_name, $value)->first();
                    if (empty($item) && isset($options['should_exist'])) {
                        $error = response()->json(['error' => 'Invalid '.$table.'.'.$key_name.' - it doesn\'t exist!', 'data' => $value], 412, []);
                    } elseif (!empty($item) && isset($options['should_not_exist'])) {
                        $error = response()->json(['error' => 'Invalid '.$table.'.'.$key_name.' - it exist but should not!', 'data' => ['table' => $table, 'object' => $item]], 412, []);
                    }
                }   
            }  

        }
        
        return $value;
    }
    
    
    public static function sanatizeDecimalInput($key_name, $value, &$error = null, $options = []) {
        $value = trim(urldecode($value));
        $value = (empty($value) && isset($options['allow_null'])) ? null : $value;
        $value = (substr($value, 0, 1) == '{' && substr($value, -1, 1) == '}') ? null : $value;
        
        if (is_null($error)) {
            
            if (!is_null($value) || (is_null($value) && !isset($options['allow_null']))) {
                if (is_null($value)) {
                    $error = response()->json(['error' => ''.$key_name.' can not be empty', 'data' => $value], 412, []);        
                } 

                $not_numeric = !is_numeric($value);
                $numeric_but_invalid = is_numeric($value) && isset($options['invalid_value']) && $options['invalid_value'] == $value;
                if (is_null($error) && ($not_numeric || $numeric_but_invalid)) {
                    // check format
                    $error = response()->json(['error' => 'Invalid '.$key_name.' value (or not accepted)', 'data' => $value], 412, []);
                } else {
                    // cast as a decimal number
                    $value = floatval($value);
                }
                
                if (is_null($error) && ($value < 1 || $value > 100)) {
                    $error = response()->json(['error' => 'Invalid '.$key_name.' value - should be between 1 and 100', 'data' => [$value]], 412, []);
                }
                
                if (is_null($error) && strlen(substr(strrchr($value, "."), 1)) > 2) {
                    $error = response()->json(['error' => 'Invalid '.$key_name.' value - max 2 decimals', 'data' => [$value]], 412, []);
                }
                
            }  

        }
        
        return $value;
    }
    
    public static function sanatizeDateInput($key_name, $value, &$error = null, $options = []) {
        $value = trim(urldecode($value));
        $value = (empty($value) && isset($options['allow_null'])) ? null : $value;
        $value = (substr($value, 0, 1) == '{' && substr($value, -1, 1) == '}') ? null : $value;
        
        if (is_null($error)) {
            
            // valid format
            $format = 'Y-m-d H:i:s';
            
            if (!is_null($value) || (is_null($value) && !isset($options['allow_null']))) {
                if (is_null($value)) {
                    $error = response()->json(['error' => ''.$key_name.' can not be empty', 'data' => $value], 412, []);        
                } 
                
                // tmp date
                $date_tmp = \DateTime::createFromFormat($format, $value);
                if (is_null($error) && (!$date_tmp || $date_tmp->format($format) != $value)) {
                    // check format
                    $error = response()->json(['error' => 'Invalid '.$key_name.' value (format showd be YYYY-MM-DD H:m:s)', 'data' => $value], 412, []);
                } 
                
                if (is_null($error) && isset($options['date_bigger_than'])) {
                    $date_value = new \DateTime($value);
                    $date_bigger_than = new \DateTime($options['date_bigger_than']);
                    
                    if ($date_value <= $date_bigger_than) {
                        $error = response()->json(['error' => 'Invalid '.$key_name.' value - should be bigger than '.$options['date_bigger_than'], 'data' => [$value]], 412, []);    
                    }
                    
                }
            }  

        }
        
        return $value;
    }
    
    public static function paginateResults($parameters) {
        $error = null;
        
        list($object, $path, $params) = $parameters['request']->route();
        $name = (empty($params) || !isset($params['name']) || urldecode($params['name']) == '{name}') ? '' : $params['name'];
        $page_number = (empty($params) || !isset($params['page_number']) || urldecode($params['page_number']) == '{page_number}') ? '' : $params['page_number'];

        # Validations
        # 1 - $name (format)
        $name = Controller::sanatizeStringInput(null, 'name', $name, $error, ['allow_null' => true, 'avoid_table_check' => true]);
        
        # 2 - $page_number (format)
        $page_number = Controller::sanatizeIntegerInput(null, 'page_number', $page_number, $error, ['allow_null' => true, 'avoid_table_check' => true, 'invalid_value' => 0]);
        
        if (is_null($error)) {
            
            $showing_per_page = (isset($parameters['showing_per_page'])) ? $parameters['showing_per_page'] : 100;
            if (is_null($page_number)) {
                $page_number = 1;
            }
            
            $query = \DB::table($parameters['table']);
            
            if (isset($parameters['filter_deleted_items']) && $parameters['filter_deleted_items']==true) {
                $query->where('deleted', '!=', 1);
            }
            if (!is_null($name)) {
                $query->where('name', 'like', $name.'%');
            }
            
            $total_elemts_qty = $query->count();
            
            $result = [
                'collection' => $query->skip($showing_per_page * ($page_number-1))->take($showing_per_page)->get(),
                'pagination' => [
                    'previous_page_number' => ($page_number > 1) ? $page_number-1 : null,
                    'current_page_number' => $page_number,
                    'next_page_number' => ($total_elemts_qty > ($page_number * $showing_per_page)) ? $page_number+1 : null,
                    'total_elemts_qty' => $total_elemts_qty,
                    'showing_per_page' => $showing_per_page
                ]
            ];
            
            return response()->json($result);
            
        } else {
            
            return $error;
            
        }
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

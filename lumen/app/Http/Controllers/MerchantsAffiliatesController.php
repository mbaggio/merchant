<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Rinvex\Country\Country as RinvexCountry;

class MerchantsAffiliatesController extends Controller
{
 
    /**
     * @OA\Post(
     *     path="/merchants-affiliate/{merchant_id}/{cash_back_rate}/{logo1_url}/{logo2_url}/{logo3_url}/{shipping_address_first_name}/{shipping_address_last_name}/{shipping_address_street}/{shipping_address_postalcode}/{shipping_address_state}/{shipping_address_country_code}/{billing_address_first_name}/{billing_address_last_name}/{billing_address_street}/{billing_address_postalcode}/{billing_address_state}/{billing_address_country_code}",
     *     description="Add merchant as affiliate",
     *     tags={"Merchants Affiliates"},
     *     @OA\Parameter(
     *        name="merchant_id",
     *        in="path",
     *        description="Merchant id",
     *        required=true
     *     ),
     *     @OA\Parameter(
     *        name="cash_back_rate",
     *        in="path",
     *        description="AdCampaign cash_back_rate (decimal number)",
     *        required=true
     *     ),
     *     @OA\Parameter(
     *        name="logo1_url",
     *        in="path",
     *        description="logo1 URL",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="logo2_url",
     *        in="path",
     *        description="logo2 URL",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="logo3_url",
     *        in="path",
     *        description="logo3 URL",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_first_name",
     *        in="path",
     *        description="Shipping First name",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_last_name",
     *        in="path",
     *        description="Shipping Last name",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_street",
     *        in="path",
     *        description="Shipping Street address",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_postalcode",
     *        in="path",
     *        description="Shipping Postal",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_state",
     *        in="path",
     *        description="Shipping State",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_country_code",
     *        in="path",
     *        description="Shipping CountryCode (a two-letter ISO 3166-1 alpha-2)",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_first_name",
     *        in="path",
     *        description="Billing First name",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_last_name",
     *        in="path",
     *        description="Billing Last name",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_street",
     *        in="path",
     *        description="Billing Street address",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_postalcode",
     *        in="path",
     *        description="Billing Postal",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_state",
     *        in="path",
     *        description="Billing State",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_country_code",
     *        in="path",
     *        description="Billing CountryCode (a three-letter - ISO 3166-1 alpha-3)",
     *        required=false
     *     ),
     *     @OA\Response(response="201", description="Merchant addedd as affiliate"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function createMerchantsAffiliation(Request $request) {
        $error = null;
        
        list($object, $path, $params) = $request->route();

        // let's store all the filled fields here
        $data = [];
        
        # 1 - $merchant_id (format and existant)
        $data['merchant_id'] = Controller::sanatizeIntegerInput('merchants', 'id', $params['merchant_id'], $error, ['should_exist' => true, 'check_logical_delete' => true]);
        
        # 2 - $cash_back_rate (format)
        $data['cash_back_rate'] = Controller::sanatizeDecimalInput('cash_back_rate', $params['cash_back_rate'], $error);
        
        # 3 - string fields
        $txt_fields = [
            'logo1_url', 'logo2_url', 'logo3_url', 'shipping_address_first_name', 'shipping_address_last_name', 'shipping_address_street', 'shipping_address_postalcode', 
            'shipping_address_state', 'billing_address_first_name', 'billing_address_last_name', 'billing_address_street', 
            'billing_address_postalcode', 'billing_address_state'
        ];
        array_walk($txt_fields, function($string_field_name) use (&$error, &$data, $params) {
            $aux_value = isset($params[$string_field_name]) ? $params[$string_field_name] : null;
            if (($aux_value = Controller::sanatizeStringInput(null, $string_field_name, $aux_value, $error, ['allow_null' => true, 'avoid_table_check' => true])) !== null) {
                $data[$string_field_name] = $aux_value;
            }
        });
        
        # 4 - country fields
        $country_fields = ['shipping_address_country_code', 'billing_address_country_code'];
        array_walk($country_fields, function($string_field_name) use (&$error, &$data, &$params) {
            $params[$string_field_name] = (isset($params[$string_field_name]) && trim(urldecode($params[$string_field_name])) != '{'.$string_field_name.'}' && trim(urldecode($params[$string_field_name])) != ',') ? trim(urldecode($params[$string_field_name])) : null;
            
            if (is_null($error) && !is_null($params[$string_field_name])) {
                $params[$string_field_name] = Controller::sanatizeStringInput(null, $string_field_name, $params[$string_field_name], $error, ['avoid_table_check' => true]);
                
                if (is_null($error)) {
                    try {
                        $tmp_country = country($params[$string_field_name]);   
                        $data[$string_field_name] = $tmp_country->getIsoAlpha2();
                    } catch (\Rinvex\Country\CountryLoaderException $e) {
                        $error = response()->json(['error' => 'Invalid '.$string_field_name.' value', 'data' => $params[$string_field_name]], 412, []);
                    }  
                }
                
            }
        });
        
        if (is_null($error)) {
            // check for unique
            $item = \DB::table('merchant_affiliates')
                ->where('merchant_id', $data['merchant_id'])
                ->first();
            if (!empty($item)) {
                $error = response()->json(['error' => 'Relationship already exists', 'data' => [$item]], 412, []);        
            }
        }
        
        if (is_null($error)) {
            
            // Save this new affiliate relationship into the DB
            \App\Models\MerchantAffiliate::create($data);
            
            // cache clear
            $cache_key = 'getMerchantsInfo:'.$data['merchant_id'];
            if (Redis::exists($cache_key)) {
                Redis::del($cache_key);
            }
            
            return response()->json(['success' => 'Merchant addedd as affiliate', 'data' => $data], 201);

        } else {
            
            return $error;
            
        }
    }
    
    
    /**
     * @OA\Patch(
     *     path="/merchants-affiliate/{merchant_id}/{cash_back_rate}/{logo1_url}/{logo2_url}/{logo3_url}/{shipping_address_first_name}/{shipping_address_last_name}/{shipping_address_street}/{shipping_address_postalcode}/{shipping_address_state}/{shipping_address_country_code}/{billing_address_first_name}/{billing_address_last_name}/{billing_address_street}/{billing_address_postalcode}/{billing_address_state}/{billing_address_country_code}",
     *     description="Update merchant affiliate data",
     *     tags={"Merchants Affiliates"},
     *     @OA\Parameter(
     *        name="merchant_id",
     *        in="path",
     *        description="Merchant id",
     *        required=true
     *     ),
     *     @OA\Parameter(
     *        name="cash_back_rate",
     *        in="path",
     *        description="AdCampaign cash_back_rate (decimal number)",
     *        required=true
     *     ),
     *     @OA\Parameter(
     *        name="logo1_url",
     *        in="path",
     *        description="logo1 URL",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="logo2_url",
     *        in="path",
     *        description="logo2 URL",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="logo3_url",
     *        in="path",
     *        description="logo3 URL",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_first_name",
     *        in="path",
     *        description="Shipping First name",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_last_name",
     *        in="path",
     *        description="Shipping Last name",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_street",
     *        in="path",
     *        description="Shipping Street address",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_postalcode",
     *        in="path",
     *        description="Shipping Postal",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_state",
     *        in="path",
     *        description="Shipping State",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="shipping_address_country_code",
     *        in="path",
     *        description="Shipping CountryCode (Format: a three-letter - ISO 3166-1 alpha-3)",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_first_name",
     *        in="path",
     *        description="Billing First name",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_last_name",
     *        in="path",
     *        description="Billing Last name",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_street",
     *        in="path",
     *        description="Billing Street address",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_postalcode",
     *        in="path",
     *        description="Billing Postal",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_state",
     *        in="path",
     *        description="Billing State",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="billing_address_country_code",
     *        in="path",
     *        description="Billing CountryCode (Format: a two-letter - ISO 3166-1 alpha-2)",
     *        required=false
     *     ),
     *     @OA\Response(response="200", description="Merchant updated"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function updateMerchantsAffiliation(Request $request) {
        $error = null;
        
        list($object, $path, $params) = $request->route();
        
        # Validations
        
        # current item
        $current_data = null;
        
        # something changed 
        $changes = [];
        
        # 1 - $params['merchant_id'] (format and existant)
        $params['merchant_id'] = Controller::sanatizeIntegerInput('merchants', 'id', $params['merchant_id'], $error, ['should_exist' => true, 'check_logical_delete' => true]);
        
        # 2 - relationship exists
        Controller::sanatizeIntegerInput('merchant_affiliates', 'merchant_id', $params['merchant_id'], $error, ['should_exist' => true]);
        
        if (is_null($error)) {
            // exists 
            $current_data = \DB::table('merchant_affiliates')->where('merchant_id', $params['merchant_id'])->first();
        }
        
        # 3 - $cash_back_rate (format)
        $params['cash_back_rate'] = trim(urldecode($params['cash_back_rate']));
        $params['cash_back_rate'] = ($params['cash_back_rate'] == '{cash_back_rate}') ? null : $params['cash_back_rate'];
        if (is_null($error) && !is_null($current_data) && !is_null($params['cash_back_rate']) && $current_data->cash_back_rate != $params['cash_back_rate']) {
            $params['cash_back_rate'] = Controller::sanatizeStringInput('merchant_affiliates', 'cash_back_rate', $params['cash_back_rate'], $error);
            $changes['cash_back_rate'] = $params['cash_back_rate'];
        }

        # 4 - strings (format)
        $txt_fields = [
            'logo1_url', 'logo2_url', 'logo3_url', 'shipping_address_first_name', 'shipping_address_last_name', 'shipping_address_street', 'shipping_address_postalcode', 
            'shipping_address_state', 'billing_address_first_name', 'billing_address_last_name', 'billing_address_street', 
            'billing_address_postalcode', 'billing_address_state'
        ];
        array_walk($txt_fields, function($string_field_name) use (&$error, &$params, &$current_data, &$changes) {
            $params[$string_field_name] = 
                (isset($params[$string_field_name]) && trim(urldecode($params[$string_field_name])) != '{'.$string_field_name.'}' && trim(urldecode($params[$string_field_name])) != ',') ? trim(urldecode($params[$string_field_name])) : null;

            if (is_null($error) && !is_null($current_data) && $current_data->$string_field_name != $params[$string_field_name]) {
                $params[$string_field_name] = Controller::sanatizeStringInput('merchant_affiliates', $string_field_name, $params[$string_field_name], $error, ['allow_null' => true, 'avoid_table_check' => true]);
                $changes[$string_field_name] = $params[$string_field_name];
            }

        });
        
        # 5 - country fields
        $country_fields = ['shipping_address_country_code', 'billing_address_country_code'];
        array_walk($country_fields, function($string_field_name) use (&$error, &$params, &$current_data, &$changes) {
            $params[$string_field_name] = (isset($params[$string_field_name]) && trim(urldecode($params[$string_field_name])) != '{'.$string_field_name.'}' && trim(urldecode($params[$string_field_name])) != ',') ? trim(urldecode($params[$string_field_name])) : null;
            
            if (is_null($error) && !is_null($current_data) && $current_data->$string_field_name != $params[$string_field_name]) {
                $params[$string_field_name] = Controller::sanatizeStringInput(null, $string_field_name, $params[$string_field_name], $error, ['allow_null' => true, 'avoid_table_check' => true]);
                
                if (!is_null($params[$string_field_name])) {
                    try {
                        $tmp_country = country($params[$string_field_name]);   
                        $changes[$string_field_name] = $tmp_country->getIsoAlpha2();
                    } catch (\Rinvex\Country\CountryLoaderException $e) {
                        $error = response()->json(['error' => 'Invalid '.$string_field_name.' value', 'data' => $params[$string_field_name]], 412, []);
                    }
                } else {
                    $changes[$string_field_name] = $params[$string_field_name]; // null
                }
            }
        });

        if (is_null($error)) {
            
            if (!empty($changes)) {
                
                $changes['updated_at'] = date('Y-m-d H:i:s');
                
                // update this new category in our DB
                \App\Models\MerchantAffiliate::where('merchant_id', $params['merchant_id'])->update($changes);

                // cache clear
                $cache_key = 'getMerchantsInfo:'.$params['merchant_id'];
                if (Redis::exists($cache_key)) {
                    Redis::del($cache_key);
                }

                return response()->json(['success' => 'Item updated', 'data' => $changes], 200);
                
            } else {
                
                return response()->json(['success' => 'Nothing to change', 'data' => ['merchant_id' => $params['merchant_id'], 'data' => $current_data, 'new_data' => $params]], 200);
                
            }

        } else {
            
            return $error;
            
        }
    }
    
        
    /**
     * @OA\Delete(
     *     path="/merchants-affiliate/{merchant_id}",
     *     description="Remove Affiliate asociation",
     *     tags={"Merchants Affiliates"},
     *     @OA\Parameter(
     *        name="merchant_id",
     *        in="path",
     *        description="Merchant id",
     *        required=true
     *     ),
     *     @OA\Response(response="200", description="Merchant deleted"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function deleteMerchantsAffiliation(Request $request, $merchant_id) {
        $error = null;
        
        # Validations
        # 1 - $merchant_id (format and existant)
        $merchant_id = Controller::sanatizeIntegerInput('merchant_affiliates', 'merchant_id', $merchant_id, $error, ['should_exist' => true]);
        
        if (is_null($error)) {
            
            // delete
            $tmp_category = clone \DB::table('merchant_affiliates')->where('merchant_id', $merchant_id)->first();
            \App\Models\MerchantAffiliate::where('merchant_id', $merchant_id)->delete();

            // cache clear
            $cache_key = 'getMerchantsInfo:'.$merchant_id;
            if (Redis::exists($cache_key)) {
                Redis::del($cache_key);
            }

            return response()->json(['success' => 'Item deleted', 'data' => $tmp_category], 200);    
            
        } else {
            
            return $error;
            
        }
        
    }
    
    /**********************************************************
    /**********************************************************
    Merchant AFFILIATES ORDERS CRUD Starts here
    /**********************************************************
    /**********************************************************/
    /**
     * @OA\Post(
     *     path="/merchants-affiliate-order/{merchant_id}/{order_amount}",
     *     description="Add merchant order",
     *     tags={"Merchants Affiliates Orders"},
     *     @OA\Parameter(
     *        name="merchant_id",
     *        in="path",
     *        description="Merchant id",
     *        required=true
     *     ),
     *     @OA\Parameter(
     *        name="order_amount",
     *        in="path",
     *        description="Order amount (decimal number)",
     *        required=true
     *     ),
     *     @OA\Response(response="201", description="Merchant Order added"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function createMerchantsAffiliateOrder(Request $request, $merchant_id, $order_amount) {
        $error = null;
        
        # Validations
        # 1 - $merchant_id (format and existant)
        $merchant_id = Controller::sanatizeIntegerInput('merchant_affiliates', 'merchant_id', $merchant_id, $error, ['should_exist' => true]);
        
        # 2 - $order_amount (format)
        $order_amount = Controller::sanatizeDecimalInput('order_amount', $order_amount, $error, ['range' => ['min' => 1, 'max'=> 10000]]);
        
        if (is_null($error)) {
            
            // get merchant affiliate info
            // get data from DB
            $merchant_affiliate_details = \DB::table('merchant_affiliates')->where('merchant_id', $merchant_id)->first();
            // $merchant_details = json_decode(@file_get_contents('http://localhost/merchants-details/'.$merchant_id), true);
            $cash_back_rate = floatval($merchant_affiliate_details->cash_back_rate);
            $comission = $order_amount * ($cash_back_rate / 100);

            //$sitemap_category_details = json_decode(@file_get_contents('http://localhost/sitemap_categories/'.$merchant_details['merchant_info']['sitemap_category_id']), true);
            // $sitemap_category_details = $sitemap_category_details['collection'][0];
            $merchant_details = \DB::table('merchants')->where('id', $merchant_id)->first();
            $sitemap_category_details = \DB::table('sitemap_categories')->where('id', $merchant_details->sitemap_category_id)->first();
            

            // send request to elastic (sitemap data)
            Controller::sendToElastic(
                'sales_sitemap', 
                'totals', 
                $sitemap_category_details->name.' ('.$sitemap_category_details->id.')', 
                ['amount' => $order_amount, 'commission' => $comission]);
                

            // send request to elastic (merchant data)
            Controller::sendToElastic(
                'sales_merchant',
                'totals', 
                $merchant_details->name.' ('.$merchant_details->id.')', 
                ['amount' => $order_amount, 'commission' => $comission]);

            
            
            return response()->json(['success' => 'Item added', 'data' => ['merchant_id' => $merchant_id, 'order_amount' => $order_amount, 'comission' => $comission]], 200);    
            
        } else {
            
            return $error;
            
        }
        
    }
    
}

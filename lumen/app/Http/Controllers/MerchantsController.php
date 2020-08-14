<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Rinvex\Country\Country as RinvexCountry;

class MerchantsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/merchants/{name}/{page_number}",
     *     description="Merchants list",
     *     tags={"Merchants"},
     *     @OA\Parameter(
     *        name="name",
     *        in="path",
     *        description="Merchant name (example: Ebay)",
     *        required=false,
     *        allowEmptyValue=true
     *     ),
     *     @OA\Parameter(
     *        name="page_number",
     *        in="path",
     *        description="Page number",
     *        required=false,
     *        example=1,
     *        allowEmptyValue=true
     *     ),
     *     @OA\Response(response="200", description="Merchants list")
     * )
     */
    public function getMerchants(Request $request) {        
        return Controller::paginateResults([
            'table' => 'merchants',
            'filter_deleted_items' => true,
            'request' => $request
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/merchants-details/{id}",
     *     description="Merchant info",
     *     tags={"Merchants"},
     *     @OA\Parameter(
     *        name="id",
     *        in="path",
     *        description="Merchant id",
     *        required=true
     *     ),
     *     @OA\Response(response="200", description="Merchants info")
     * )
     */
    public function getMerchantsInfo(Request $request, $id) {
        
        $cache_key = 'getMerchantsInfo:'.$id;
        if (Redis::exists($cache_key)) {
            
            // get data from redis
            $data = json_decode(Redis::get($cache_key), true);
            $data['cache_data'] = true;
            $return = response()->json($data, 200);
            
        } else {

            // get data from DB
            $data = MerchantsController::getMerchantsInfoFromDB($request, $id);
            
            if (is_array($data) && isset($data['success'])) {
                // save in Redis
                Redis::set($cache_key, json_encode($data));
                Redis::expire($cache_key, 60);
                $data['cache_data'] = false;
                
                $return = response()->json($data, 200);
                
            } else {
                
                $return = $data;
                
            }
            

        }
        
        return $return;
    }
    
    public static function getMerchantsInfoFromDB(Request $request, $id) {
        
        $error = null;
        $current_object_data = null;
        
        # 1 - $id (format and existant)
        $id = Controller::sanatizeIntegerInput('merchants', 'id', $id, $error, ['should_exist' => true]);
        
        if (is_null($error)) {
            
            // exists 
            $current_object_data = \DB::table('merchants')->where('id', $id)->first();
            
            if ($current_object_data->deleted == 1) {
                $error = response()->json(['error' => 'Invalid merchant (deleted)', 'data' => ['table' => 'merchants', 'object' => $current_object_data]], 412, []);        
            } 
            
        }
        
        if (is_null($error)) {
            
            $return = [];
            $return['success'] = true;
            $return['merchant_info'] = $current_object_data;
            $return['active_campaigns'] = \DB::table('ad_campaign_merchants')
                ->join('ad_campaigns', 'ad_campaigns.id', '=', 'ad_campaign_merchants.ad_campaign_id')
                ->where('ad_campaign_merchants.merchant_id', '=', $id)
                ->where('ad_campaigns.deleted', '!=', 1)
                ->select('ad_campaigns.*')
                ->get();
            
            $affiliate_data = \DB::table('merchant_affiliates')
                ->where('merchant_id', $id)
                ->first();
            $return['affiliate_data'] = !empty($affiliate_data) ? $affiliate_data : null;

            return $return;   
            
        } else {
            
            return $error;
            
        }
    }
    
    
    /**
     * @OA\Post(
     *     path="/merchants/{name}/{url}/{description}/{sitemap_category_id}",
     *     description="New Merchant",
     *     tags={"Merchants"},
     *     @OA\Parameter(
     *        name="name",
     *        in="path",
     *        description="Merchant name (Example: Ebay)",
     *        required=true
     *     ),
     *     @OA\Parameter(
     *        name="url",
     *        in="path",
     *        description="Merchant URL (example: http://www.ebay.com)",
     *        required=true
     *     ),
     *     @OA\Parameter(
     *        name="description",
     *        in="path",
     *        description="Merchant Description (long text)",
     *        required=true
     *     ),
     *     @OA\Parameter(
     *        name="sitemap_category_id",
     *        in="path",
     *        description="Sitemap Category ID",
     *        required=true
     *     ),
     *     @OA\Response(response="201", description="New Merchant addedd"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function create(Request $request, $name, $url, $description, $sitemap_category_id) {
        $error = null;
        
        # Validations
        # 1 - $name (format and existant)
        $name = Controller::sanatizeStringInput('merchants', 'name', $name, $error);
        
        # 2 - $url (format and existant)
        $url = Controller::sanatizeStringInput('merchants', 'url', $url, $error);
        
        # 3 - $description (format)
        $description = Controller::sanatizeStringInput('merchants', 'description', $description, $error, ['avoid_table_check' => true]);
        
        # 4 - $sitemap_category_id (format and existant)
        $sitemap_category_id = Controller::sanatizeIntegerInput('sitemap_categories', 'id', $sitemap_category_id, $error, ['should_exist' => true, 'invalid_value' => 1]); 
        
        if (is_null($error)) {            
            
            // Save this new category in our DB
            $new = \App\Models\Merchant::create([
                'name' => $name,
                'url' => $url,
                'description' => $description,
                'sitemap_category_id' => $sitemap_category_id
            ]);
            
            return response()->json(['success' => 'Item addedd', 'data' => $new], 201);    

        } else {
            
            return $error;
            
        }
    }
    
    
    /**
     * @OA\Patch(
     *     path="/merchants/{id}/{new_name}/{new_url}/{new_description}/{new_sitemap_category_id}",
     *     description="Update merchant",
     *     tags={"Merchants"},
     *     @OA\Parameter(
     *        name="id",
     *        in="path",
     *        description="Merchant ID",
     *        required=true
     *     ),
     *     @OA\Parameter(
     *        name="new_name",
     *        in="path",
     *        description="Merchant new name",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="new_url",
     *        in="path",
     *        description="Merchant new url",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="new_description",
     *        in="path",
     *        description="Merchant new description",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="new_sitemap_category_id",
     *        in="path",
     *        description="Merchant new sitemap_category_id",
     *        required=false
     *     ),
     *     @OA\Response(response="200", description="Merchant updated"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function update(Request $request, $id, $new_name, $new_url, $new_description, $new_sitemap_category_id) {
        $error = null;
        
        # Validations
        # 1 - $id (format and existant)
        # 2 - $new_name (format and existant)
        # 3 - $new_url (format and existant)
        # 4 - $new_description (format)
        # 5 - $new_sitemap_category_id (format)
        
        # current item
        $current_data = null;
        
        # something changed 
        $changes = [];
        
        # 1 - $id (format and existant)
        $id = Controller::sanatizeIntegerInput('merchants', 'id', $id, $error, ['should_exist' => true, 'check_logical_delete' => true]);
        
        if (is_null($error)) {
            
            // exists 
            $current_data = \DB::table('merchants')->where('id', $id)->first();
            
        }
        
        # 2 - $new_name
        $new_name = trim(urldecode($new_name));
        $new_name = ($new_name == '{new_name}' || $new_name == ',') ? null : $new_name;
        if (is_null($error) && !is_null($current_data) && !is_null($new_name) && $current_data->name != $new_name) {
            $new_name = Controller::sanatizeStringInput('merchants', 'name', $new_name, $error);
            $changes['name'] = $new_name;
        }

        # 3 - $new_url
        $new_url = trim(urldecode($new_url));
        $new_url = ($new_url == '{new_url}' || $new_url == ',') ? null : $new_url;
        if (is_null($error) && !is_null($current_data) && !is_null($new_url) && $current_data->url != $new_url) {
            $new_url = Controller::sanatizeStringInput('merchants', 'url', $new_url, $error, ['allow_null' => true]);
            $changes['url'] = $new_url;
        }
        
        # 4 - $new_description
        $new_description = trim(urldecode($new_description));
        $new_description = ($new_description == '{new_description}') ? null : $new_description;
        if (is_null($error) && !is_null($current_data) && $current_data->description != $new_description) {
            $new_description = Controller::sanatizeStringInput('merchants', 'description', $new_description, $error, ['allow_null' => true, 'avoid_table_check' => true]);
            $changes['description'] = $new_description;
        }
        
        # 5 - $new_sitemap_category_id
        $new_sitemap_category_id = trim(urldecode($new_sitemap_category_id));
        $new_sitemap_category_id = ($new_sitemap_category_id == '{new_sitemap_category_id}' || $new_sitemap_category_id == ',') ? null : $new_sitemap_category_id;
        if (is_null($error) && !is_null($current_data) && !is_null($new_sitemap_category_id) && $current_data->sitemap_category_id != $new_sitemap_category_id) {
            $new_sitemap_category_id = Controller::sanatizeIntegerInput('sitemap_categories', 'id', $new_sitemap_category_id, $error, ['should_exist' => true, 'invalid_value' => 1]); 
            $changes['sitemap_category_id'] = $new_sitemap_category_id;
        }

        if (is_null($error)) {
            
            if (!empty($changes)) {
                
                $changes['updated_at'] = date('Y-m-d H:i:s');
                
                // update this new category in our DB
                \App\Models\Merchant::where('id', $id)->update($changes);

                // cache clear
                $cache_key = 'getMerchantsInfo:'.$id;
                if (Redis::exists($cache_key)) {
                    Redis::del($cache_key);
                }

                return response()->json(['success' => 'Item updated', 'data' => ['id' => $id, 'data' => $changes]], 200);
                
            } else {
                
                return response()->json(['success' => 'Nothing to change', 'data' => ['id' => $id, 'data' => $current_data]], 200);
                
            }
            

        } else {
            
            return $error;
            
        }
    }
    
    
    /**
     * @OA\Delete(
     *     path="/merchants/{id}",
     *     description="Delete Merchant",
     *     tags={"Merchants"},
     *     @OA\Parameter(
     *        name="id",
     *        in="path",
     *        description="Merchant id",
     *        required=true
     *     ),
     *     @OA\Response(response="200", description="Merchant deleted"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function delete(Request $request, $id) {
        $error = null;
        
        # Validations
        # 1 - $id (format and existant)
        $id = Controller::sanatizeIntegerInput('merchants', 'id', $id, $error, ['should_exist' => true, 'check_logical_delete' => true]);
            
        # 2 - $id (existant Affiliate relationships)
        // Controller::sanatizeIntegerInput('sitemap_categories', 'parent_id', $id, $error, ['should_not_exist' => true]);
        
        # 3 - $id (existant Ad Campaigns relationships)
        // Controller::sanatizeIntegerInput('merchants', 'sitemap_category_id', $id, $error, ['should_not_exist' => true]);
            
        if (is_null($error)) {
            
            // delete
            $tmp_object = \DB::table('merchants')->where('id', $id)->first();
            
            // Logical deletion
            if ($tmp_object->deleted == false) {
                \App\Models\Merchant::where('id', $id)->update([
                    'name' => $tmp_object->name.' - DELETED #' . $id,
                    'url' => $tmp_object->url.' - DELETED #' . $id,
                    'deleted' => 1,
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'sitemap_category_id' => 1 // internal sitemap_category_id for deleted merchants
                ]);
            }
            
            // cache clear
            $cache_key = 'getMerchantsInfo:'.$id;
            if (Redis::exists($cache_key)) {
                Redis::del($cache_key);
            }
            
            return response()->json(['success' => 'Item deleted', 'data' => $tmp_object], 200);    
            
        } else {
            
            return $error;
            
        }
        
    }
    
    
    /**********************************************************
    /**********************************************************
    Merchant AFFILIATES CRUD Starts here
    /**********************************************************
    /**********************************************************/
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
    
}

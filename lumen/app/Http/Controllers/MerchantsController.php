<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
     *        description="Merchant name",
     *        required=false,
     *        example="Ebay",
     *        allowEmptyValue=true,
     *     ),
     *     @OA\Parameter(
     *        name="page_number",
     *        in="path",
     *        description="Page number",
     *        required=false,
     *        example=1,
     *        allowEmptyValue=true,
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
     * @OA\Post(
     *     path="/merchants/{name}/{url}/{description}/{sitemap_category_id}",
     *     description="New Merchant",
     *     tags={"Merchants"},
     *     @OA\Parameter(
     *        name="name",
     *        in="path",
     *        description="Merchant name",
     *        required=true,
     *        example="Ebay"
     *     ),
     *     @OA\Parameter(
     *        name="url",
     *        in="path",
     *        description="Merchant URL",
     *        required=true,
     *        example="http://www.ebay.com"
     *     ),
     *     @OA\Parameter(
     *        name="description",
     *        in="path",
     *        description="Merchant Description",
     *        required=true,
     *        example="eBay Inc. is a global commerce leader that connects millions of buyers and sellers around the world. We exist to enable economic opportunity for individuals, entrepreneurs, businesses and organizations of all sizes. Our portfolio of brands includes eBay Marketplace and eBay Classifieds Group, operating in 190 markets around the world."
     *     ),
     *     @OA\Parameter(
     *        name="sitemap_category_id",
     *        in="path",
     *        description="Merchant Sitemap Category ID",
     *        required=true,
     *        example="1"
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
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Parameter(
     *        name="new_name",
     *        in="path",
     *        description="Merchant new name",
     *        example="NY News Paper",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="new_url",
     *        in="path",
     *        description="Merchant new url",
     *        example="http://www.example.com",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="new_description",
     *        in="path",
     *        description="Merchant new description",
     *        example="",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="new_sitemap_category_id",
     *        in="path",
     *        description="Merchant new sitemap_category_id",
     *        example="1",
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
        $current_merchant_data = null;
        
        # something changed 
        $changes = [];
        
        # 1 - $id (format and existant)
        $id = Controller::sanatizeIntegerInput('merchants', 'id', $id, $error, ['should_exist' => true]);
        
        if (is_null($error)) {
            
            // exists 
            $current_merchant_data = \DB::table('merchants')->where('id', $id)->first();
            
            if ($current_merchant_data->deleted == 1) {
                $error = response()->json(['error' => 'Invalid merchant (deleted)', 'data' => ['table' => 'merchants', 'object' => $current_merchant_data]], 412, []);        
            } 
            
        }
        
        # 2 - $new_name
        $new_name = trim(urldecode($new_name));
        $new_name = ($new_name == '{new_name}') ? null : $new_name;
        if (is_null($error) && !is_null($current_merchant_data) && !is_null($new_name) && $current_merchant_data->name != $new_name) {
            $new_name = Controller::sanatizeStringInput('merchants', 'name', $new_name, $error);
            $changes['name'] = $new_name;
        }

        # 3 - $new_url
        $new_url = trim(urldecode($new_url));
        $new_url = ($new_url == '{new_url}') ? null : $new_url;
        if (is_null($error) && !is_null($current_merchant_data) && $current_merchant_data->url != $new_url) {
            $new_url = Controller::sanatizeStringInput('merchants', 'url', $new_url, $error);
            $changes['url'] = $new_url;
        }
        
        # 3 - $new_description
        $new_description = trim(urldecode($new_description));
        $new_description = ($new_description == '{new_description}') ? null : $new_description;
        if (is_null($error) && !is_null($current_merchant_data) && $current_merchant_data->description != $new_description) {
            $new_description = Controller::sanatizeStringInput('merchants', 'description', $new_description, $error, ['avoid_table_check' => true]);
            $changes['description'] = $new_description;
        }
        

        $new_sitemap_category_id = trim(urldecode($new_sitemap_category_id));
        $new_sitemap_category_id = ($new_sitemap_category_id == '{new_sitemap_category_id}') ? null : $new_sitemap_category_id;
        if (is_null($error) && !is_null($current_merchant_data) && $current_merchant_data->sitemap_category_id != $new_sitemap_category_id) {
            $new_sitemap_category_id = Controller::sanatizeIntegerInput('sitemap_categories', 'id', $new_sitemap_category_id, $error, ['should_exist' => true, 'invalid_value' => 1]); 
            $changes['sitemap_category_id'] = $new_sitemap_category_id;
        }

        if (is_null($error)) {
            
            if (!empty($changes)) {
                
                $changes['updated_at'] = date('Y-m-d H:i:s');
                
                // update this new category in our DB
                \App\Models\Merchant::where('id', $id)->update($changes);

                // store it in elastic
                // $this->sendToElastic('info', 'tag_unico', 'New Category "'.$valor.'"');

                return response()->json(['success' => 'Item updated', 'data' => ['id' => $id, 'data' => $changes]], 200);
                
            } else {
                
                return response()->json(['success' => 'Nothing to change', 'data' => ['id' => $id, 'data' => $current_merchant_data]], 200);
                
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
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Response(response="200", description="Merchant deleted"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function delete(Request $request, $id) {
        $error = null;
        
        # Validations
        # 1 - $id (format and existant)
        $id = Controller::sanatizeIntegerInput('merchants', 'id', $id, $error, ['should_exist' => true]);
            
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
            
            return response()->json(['success' => 'Item deleted', 'data' => $tmp_object], 200);    
            
        } else {
            
            return $error;
            
        }
        
    }
    
}

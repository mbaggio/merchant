<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdcampaignsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/adcampaigns/{name}/{page_number}",
     *     description="AdCampaigns list",
     *     tags={"AdCampaigns"},
     *     @OA\Parameter(
     *        name="name",
     *        in="path",
     *        description="AdCampaign name",
     *        required=false,
     *        example="Xmas",
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
     *     @OA\Response(response="200", description="AdCampaigns list")
     * )
     */
    public function getAdcampaigns(Request $request) {
        return Controller::paginateResults([
            'table' => 'ad_campaigns',
            'filter_deleted_items' => true,
            'request' => $request
        ]);
    }
    
    /**
     * @OA\Post(
     *     path="/adcampaigns/{name}/{cash_back_rate}/{date_from}/{date_to}",
     *     description="New AdCampaign",
     *     tags={"AdCampaigns"},
     *     @OA\Parameter(
     *        name="name",
     *        in="path",
     *        description="AdCampaign name",
     *        required=true,
     *        example="Xmas in July"
     *     ),
     *     @OA\Parameter(
     *        name="cash_back_rate",
     *        in="path",
     *        description="AdCampaign cash_back_rate (in %)",
     *        required=true,
     *        example="5.5"
     *     ),
     *     @OA\Parameter(
     *        name="date_from",
     *        in="path",
     *        description="AdCampaign Start date",
     *        required=true,
     *        example="2020-07-01 15:00:00"
     *     ),
     *     @OA\Parameter(
     *        name="date_to",
     *        in="path",
     *        description="AdCampaign End date",
     *        required=true,
     *        example="2020-07-03 15:00:00"
     *     ),
     *     @OA\Response(response="201", description="New AdCampaign addedd"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function create(Request $request, $name, $cash_back_rate, $date_from, $date_to) {
        $error = null;
        
        # Validations
        # 1 - $name (format and existant)
        $name = Controller::sanatizeStringInput('ad_campaigns', 'name', $name, $error);
        
        # 2 - $cash_back_rate (format)
        $cash_back_rate = Controller::sanatizeDecimalInput('cash_back_rate', $cash_back_rate, $error);
        
        # 3 - $date_from (format)
        $date_from = Controller::sanatizeDateInput('date_from', $date_from, $error);
        
        # 4 - $date_to (format)
        $date_to = Controller::sanatizeDateInput('date_to', $date_to, $error, ['date_bigger_than' => $date_from]);
        
        if (is_null($error)) {            
            
            // Save this new ad_campaign in our DB
            $new = \App\Models\AdCampaign::create([
                'name' => $name,
                'cash_back_rate' => $cash_back_rate,
                'date_from' => $date_from,
                'date_to' => $date_to
            ]);
            
            return response()->json(['success' => 'Item addedd', 'data' => [$name, $cash_back_rate, $date_from, $date_to]], 201);    

        } else {
            
            return $error;
            
        }
    }
    
    
    /**
     * @OA\Patch(
     *     path="/adcampaigns/{id}/{new_name}/{new_cash_back_rate}/{new_date_from}/{new_date_to}",
     *     description="Update AdCampaign",
     *     tags={"AdCampaigns"},
     *     @OA\Parameter(
     *        name="id",
     *        in="path",
     *        description="AdCampaign ID",
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Parameter(
     *        name="new_name",
     *        in="path",
     *        description="AdCampaign new name",
     *        example="Xmas in December",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="new_cash_back_rate",
     *        in="path",
     *        description="Adcampaign new cash_back_rate",
     *        example="10.2",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="new_date_from",
     *        in="path",
     *        description="Adcampaign new date_from",
     *        example="2020-02-02",
     *        required=false
     *     ),
     *     @OA\Parameter(
     *        name="new_date_to",
     *        in="path",
     *        description="Adcampaign new date_to",
     *        example="2020-02-20",
     *        required=false
     *     ),
     *     @OA\Response(response="200", description="Adcampaign updated"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function update(Request $request, $id, $new_name, $new_cash_back_rate, $new_date_from, $new_date_to) {
        $error = null;
        
        # Validations
        # 1 - $id (format and existant)
        # 2 - $new_name (format and existant)
        # 3 - $new_cash_back_rate (format)
        # 4 - $new_date_from (format)
        # 5 - $new_date_to (format)
        
        # current item
        $current_object_data = null;
        
        # something changed 
        $changes = [];
        
        # 1 - $id (format and existant)
        $id = Controller::sanatizeIntegerInput('ad_campaigns', 'id', $id, $error, ['should_exist' => true]);
        
        if (is_null($error)) {
            
            // exists 
            $current_object_data = \DB::table('ad_campaigns')->where('id', $id)->first();
            
            if ($current_object_data->deleted == 1) {
                $error = response()->json(['error' => 'Invalid AdCampaign (deleted)', 'data' => ['table' => 'ad_campaigns', 'object' => $current_object_data]], 412, []);        
            } 
            
        }
        
        # 2 - $new_name
        $new_name = trim(urldecode($new_name));
        $new_name = ($new_name == '{new_name}') ? null : $new_name;
        if (is_null($error) && !is_null($current_object_data) && !is_null($new_name) && $current_object_data->name != $new_name) {
            $new_name = Controller::sanatizeStringInput('ad_campaigns', 'name', $new_name, $error);
            $changes['name'] = $new_name;
        }

        # 3 - $new_cash_back_rate
        $new_cash_back_rate = trim(urldecode($new_cash_back_rate));
        $new_cash_back_rate = ($new_cash_back_rate == '{new_cash_back_rate}') ? null : $new_cash_back_rate;
        if (is_null($error) && !is_null($current_object_data) && $current_object_data->cash_back_rate != $new_cash_back_rate) {
            $new_cash_back_rate = Controller::sanatizeDecimalInput('cash_back_rate', $new_cash_back_rate, $error);
            $changes['cash_back_rate'] = $new_cash_back_rate;
        }
        
        # 4 - $new_date_from
        $new_date_from = trim(urldecode($new_date_from));
        $new_date_from = ($new_date_from == '{new_date_from}') ? null : $new_date_from;
        if (is_null($error) && !is_null($current_object_data) && $current_object_data->date_from != $new_date_from && !is_null($new_date_from)) {
            $new_date_from = Controller::sanatizeDateInput('new_date_from', $new_date_from, $error);
            $changes['date_from'] = $new_date_from;
        }

        # 5 - $new_date_to
        $new_date_to = trim(urldecode($new_date_to));
        $new_date_to = ($new_date_to == '{new_date_to}') ? null : $new_date_to;
        if (is_null($error) && !is_null($current_object_data) && $current_object_data->date_to != $new_date_to && !is_null($new_date_to)) {
            $new_date_to = Controller::sanatizeDateInput('new_date_to', $new_date_to, $error);
            $changes['date_to'] = $new_date_to;
        }

        # 6 - date still match (start vs end)
        $final_start_date = isset($changes['date_from']) ? $changes['date_from'] : $current_object_data->date_from;
        $final_end_date = isset($changes['date_to']) ? $changes['date_to'] : $current_object_data->date_to;
        Controller::sanatizeDateInput('date_to', $final_end_date, $error, ['date_bigger_than' => $final_start_date]);
        
        
        if (is_null($error)) {
            
            if (!empty($changes)) {
                
                $changes['updated_at'] = date('Y-m-d H:i:s');
                
                // update this new category in our DB
                \App\Models\AdCampaign::where('id', $id)->update($changes);

                // store it in elastic
                // $this->sendToElastic('info', 'tag_unico', 'New Category "'.$valor.'"');

                return response()->json(['success' => 'Item updated', 'data' => ['id' => $id, 'data' => $changes]], 200);
                
            } else {
                
                return response()->json(['success' => 'Nothing to change', 'data' => ['id' => $id, 'data' => $current_object_data]], 200);
                
            }
            

        } else {
            
            return $error;
            
        }
    }
    
    /**
     * @OA\Delete(
     *     path="/adcampaigns/{id}",
     *     description="Delete Adcampaign",
     *     tags={"AdCampaigns"},
     *     @OA\Parameter(
     *        name="id",
     *        in="path",
     *        description="Adcampaign id",
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Response(response="200", description="Adcampaign deleted"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function delete(Request $request, $id) {
        $error = null;
        
        # Validations
        # 1 - $id (format and existant)
        $id = Controller::sanatizeIntegerInput('ad_campaigns', 'id', $id, $error, ['should_exist' => true]);
            
        # 2 - $id (existant Affiliate relationships)
        // Controller::sanatizeIntegerInput('sitemap_categories', 'parent_id', $id, $error, ['should_not_exist' => true]);
        
        # 3 - $id (existant Ad Campaigns relationships)
        // Controller::sanatizeIntegerInput('merchants', 'sitemap_category_id', $id, $error, ['should_not_exist' => true]);
            
        if (is_null($error)) {
            
            // delete
            $tmp_object = \DB::table('ad_campaigns')->where('id', $id)->first();
            
            // Logical deletion
            if ($tmp_object->deleted == false) {
                \App\Models\AdCampaign::where('id', $id)->update([
                    'name' => $tmp_object->name.' - DELETED #' . $id,
                    'deleted' => 1,
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            return response()->json(['success' => 'Item deleted', 'data' => $tmp_object], 200);    
            
        } else {
            
            return $error;
            
        }
        
    }
    
    /**********************************************************
    /**********************************************************
    Ad Campaigns Merchants CRUD Starts here
    /**********************************************************
    /**********************************************************/
    /**
     * @OA\Get(
     *     path="/adcampaigns/{adcampaign_id}/merchants/{page_number}",
     *     description="AdCampaigns merchants list",
     *     tags={"AdCampaigns-Merchants"},
     *     @OA\Parameter(
     *        name="adcampaign_id",
     *        in="path",
     *        description="AdCampaign ID",
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Parameter(
     *        name="page_number",
     *        in="path",
     *        description="Page number",
     *        required=false,
     *        example=1,
     *        allowEmptyValue=true,
     *     ),
     *     @OA\Response(response="200", description="AdCampaigns merchants list")
     * )
     */
    public function getAdcampaignMerchants(Request $request, $adcampaign_id, $page_number) {
        $error = null;
        
        # 1 - $adcampaign_id (format and existant)
        $adcampaign_id = Controller::sanatizeIntegerInput('ad_campaigns', 'id', $adcampaign_id, $error, ['should_exist' => true]);
        
        if (is_null($error)) {
            
            // exists 
            $current_object_data = \DB::table('ad_campaigns')->where('id', $adcampaign_id)->first();
            
            if ($current_object_data->deleted == 1) {
                $error = response()->json(['error' => 'Invalid adcampaign (deleted)', 'data' => ['table' => 'ad_campaigns', 'object' => $current_object_data]], 412, []);        
            } 
            
        }
        
        if (is_null($error)) {
            
            $query = \DB::table('merchants')
                ->join('ad_campaign_merchants', 'merchants.id', '=', 'ad_campaign_merchants.merchant_id')
                ->join('ad_campaigns', 'ad_campaigns.id', '=', 'ad_campaign_merchants.ad_campaign_id')
                ->where('merchants.deleted', '!=', 1)
                ->where('ad_campaigns.deleted', '!=', 1)
                ->where('ad_campaigns.id', '=', $adcampaign_id)
                ->select('merchants.*');
            
            return Controller::paginateQueryResults([
                'query' => $query,
                'page_number' => $page_number
            ]);
        
        } else {
            
            return $error;
            
        }
        
    }
    
    
    /**
     * @OA\Post(
     *     path="/adcampaigns/{adcampaign_id}/merchants/{merchant_id}",
     *     description="New merchant in AdCampaign",
     *     tags={"AdCampaigns-Merchants"},
     *     @OA\Parameter(
     *        name="adcampaign_id",
     *        in="path",
     *        description="AdCampaign id",
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Parameter(
     *        name="merchant_id",
     *        in="path",
     *        description="Merchant id",
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Response(response="201", description="Merchant addedd"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function createAdcampaignMerchants(Request $request, $adcampaign_id, $merchant_id) {
        $error = null;
        
        # 1 - $adcampaign_id (format and existant)
        $adcampaign_id = Controller::sanatizeIntegerInput('ad_campaigns', 'id', $adcampaign_id, $error, ['should_exist' => true, 'check_logical_delete' => true]);
        
        # 1 - $merchant_id (format and existant)
        $merchant_id = Controller::sanatizeIntegerInput('merchants', 'id', $merchant_id, $error, ['should_exist' => true, 'check_logical_delete' => true]);
        
        // check for unique
        $item = \DB::table('ad_campaign_merchants')
            ->where('ad_campaign_id', $adcampaign_id)
            ->where('merchant_id', $merchant_id)
            ->first();
        if (!empty($item)) {
            $error = response()->json(['error' => 'Relationship already exists', 'data' => [$item]], 412, []);        
        }
        
        
        if (is_null($error)) {
            
            // Save this new ad_campaign in our DB
            $new = \App\Models\AdCampaignMerchant::create([
                'ad_campaign_id' => $adcampaign_id,
                'merchant_id' => $merchant_id
            ]);
            
            return response()->json(['success' => 'Item addedd', 'data' => $new], 201);

        } else {
            
            return $error;
            
        }
    }
    
    /**
     * @OA\Delete(
     *     path="/adcampaigns/{adcampaign_id}/merchants/{merchant_id}",
     *     description="Remove Merchant from Adcampaign",
     *     tags={"AdCampaigns-Merchants"},
     *     @OA\Parameter(
     *        name="adcampaign_id",
     *        in="path",
     *        description="AdCampaign id",
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Parameter(
     *        name="merchant_id",
     *        in="path",
     *        description="Merchant id",
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Response(response="200", description="Adcampaign deleted"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function deleteAdcampaignMerchants(Request $request, $adcampaign_id, $merchant_id) {
        $error = null;
        
        # 1 - $adcampaign_id (format and existant)
        $adcampaign_id = Controller::sanatizeIntegerInput('ad_campaigns', 'id', $adcampaign_id, $error, ['should_exist' => true]);
        
        # 2 - $merchant_id (format and existant)
        $merchant_id = Controller::sanatizeIntegerInput('merchants', 'id', $merchant_id, $error, ['should_exist' => true]);
        
        # 3 - previous relationship
        $tmp_object = \DB::table('ad_campaign_merchants')
                ->where('ad_campaign_id', $adcampaign_id)
                ->where('merchant_id', $merchant_id)
                ->first();
        if (empty($tmp_object)) {
            $error = response()->json(['error' => 'Relationship does not exists', 'data' => ['ad_campaign_id' => $adcampaign_id, 'merchant_id' => $merchant_id ]], 412, []);        
        }
        
        if (is_null($error)) {

            // delete
            \DB::table('ad_campaign_merchants')
                ->where('ad_campaign_id', $adcampaign_id)
                ->where('merchant_id', $merchant_id)
                ->delete();
            
            return response()->json(['success' => 'Item deleted', 'data' => ['ad_campaign_id' => $adcampaign_id, 'merchant_id' => $merchant_id ]], 200);
        
        } else {
            
            return $error;
            
        }
        
        
    }
    
    
}

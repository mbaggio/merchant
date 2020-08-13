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
}

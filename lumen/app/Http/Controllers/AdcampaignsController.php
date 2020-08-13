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
}

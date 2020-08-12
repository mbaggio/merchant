<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MerchantsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/merchants",
     *     description="Merchants list",
     *     tags={"Merchants"},
     *     @OA\Response(response="200", description="Merchants list")
     * )
     */
    
     // *        allowEmptyValue=true,
    
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
        $sitemap_category_id = Controller::sanatizeIntegerInput('sitemap_categories', 'id', $sitemap_category_id, $error, ['should_exist' => true]); 
        
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
    
    
}

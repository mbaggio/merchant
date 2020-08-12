<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Elasticsearch\ClientBuilder;

class SitemapCategoriesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/sitemap_categories",
     *     description="Sitemap Categories list",
     *     tags={"Sitemap categories"},
     *     @OA\Response(response="200", description="Sitemap cateogories list")
     * )
     */
    
    
    /**
     * @OA\Post(
     *     path="/sitemap_categories/{name}/{parent_id}",
     *     description="New Sitemap Category",
     *     tags={"Sitemap categories"},
     *     @OA\Parameter(
     *        name="name",
     *        in="path",
     *        description="Category name",
     *        required=true,
     *        example="NY News papers"
     *     ),
     *     @OA\Parameter(
     *        name="parent_id",
     *        in="path",
     *        description="Category parent id",
     *        required=false,
     *        allowEmptyValue=true,
     *        example=12
     *     ),
     *     @OA\Response(response="201", description="New Sitemap cateogory addedd"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function create(Request $request, $name, $parent_id = null) {
        $error = null;
        
        # Validations
        # 1 - $name (format and existant)
        # 2 - $parent_id (format and existant)
        
        # 1 - $name
        $name = Controller::sanatizeStringInput('sitemap_categories', 'name', $name, $error);
        
        # 2 - $parent_id (format and existant)
        $parent_id = Controller::sanatizeIntegerInput('sitemap_categories', 'id', $parent_id, $error, ['allow_null' => true, 'should_exist' => true, 'invalid_value' => 1]);
        
        if (is_null($error)) {            
                
            // Save this new category in our DB
            $new_sc = \App\Models\SitemapCategory::create([
                'name' => $name,
                'parent_id' => $parent_id
            ]);

            // store it in elastic
            // $this->sendToElastic('info', 'tag_unico', 'New Category "'.$valor.'"');
            
            return response()->json(['success' => 'Item addedd', 'data' => $new_sc], 201);    

        } else {
            
            return $error;
            
        }
    }
    
    /**
     * @OA\Patch(
     *     path="/sitemap_categories/{id}/{new_name}",
     *     description="Update Sitemap Category name",
     *     tags={"Sitemap categories"},
     *     @OA\Parameter(
     *        name="id",
     *        in="path",
     *        description="Category ID",
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Parameter(
     *        name="new_name",
     *        in="path",
     *        description="Category new name",
     *        required=true,
     *        example="NY News Paper"
     *     ),
     *     @OA\Response(response="200", description="Sitemap cateogory updated"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function update(Request $request, $id, $new_name) {
        $error = null;
        
        # Validations
        # 1 - $id (format and existant)
        # 2 - $new_name (format and existant)
        
        # 1 - $id (format and existant)
        $id = Controller::sanatizeIntegerInput('sitemap_categories', 'id', $id, $error, ['should_exist' => true, 'invalid_value' => 1]);
        
        # 2 - $new_name
        $new_name = Controller::sanatizeStringInput('sitemap_categories', 'name', $new_name, $error);
        
        if (is_null($error)) {            
            
            // update this new category in our DB
            \App\Models\SitemapCategory::where('id', $id)->update([
                'name' => $new_name,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // store it in elastic
            // $this->sendToElastic('info', 'tag_unico', 'New Category "'.$valor.'"');
            
            return response()->json(['success' => 'Item updated', 'data' => ['id' => $id, 'new_name' => $new_name]], 200);

        } else {
            
            return $error;
            
        }
    }
    
    /**
     * @OA\Delete(
     *     path="/sitemap_categories/{id}",
     *     description="Delete Sitemap Category",
     *     tags={"Sitemap categories"},
     *     @OA\Parameter(
     *        name="id",
     *        in="path",
     *        description="Category id",
     *        required=true,
     *        example="1"
     *     ),
     *     @OA\Response(response="200", description="Sitemap cateogory deleted"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function delete(Request $request, $id) {
        $error = null;
        
        # Validations
        # 1 - $id (format and existant)
        $id = Controller::sanatizeIntegerInput('sitemap_categories', 'id', $id, $error, ['should_exist' => true, 'invalid_value' => 1]);
            
        # 2 - $id (existant Categories relationships)
        Controller::sanatizeIntegerInput('sitemap_categories', 'parent_id', $id, $error, ['should_not_exist' => true]);
        
        # 2 - $id (existant Merchants relationships)
        Controller::sanatizeIntegerInput('merchants', 'sitemap_category_id', $id, $error, ['should_not_exist' => true]);
        
        if (is_null($error)) {
            
            // delete
            $tmp_category = \DB::table('sitemap_categories')->where('id', $id)->first();
            \DB::table('sitemap_categories')->delete($id);
            
            return response()->json(['success' => 'Item deleted', 'data' => $tmp_category], 200);    
            
        } else {
            
            return $error;
            
        }
        
    }
}

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
        $valor = (urldecode($name) == '{name}') ? null : trim(urldecode($name));
        if (is_null($valor)) {
            $error = response()->json(['error' => 'Name can not be empty', 'data' => $valor], 412, []);        
        } else {
            // search for that name - similar category check
            $item = \DB::table('sitemap_categories')->where('name', $valor)->first();
            
            if (!empty($item)) {
                $error = response()->json(['error' => 'Already exists', 'data' => $valor], 412, []);        
            }
        }
        
        # 2 - $parent_id (format and existant)
        $parent_id = (urldecode($parent_id) == '{parent_id}') ? null : trim($parent_id);
        if (is_null($error) && !is_null($parent_id)) {
            if (!is_numeric($parent_id)) {
                // check format
                $error = response()->json(['error' => 'Invalid parent_id value', 'data' => $parent_id], 412, []);
            } else {
                // check existant category
                $item = \DB::table('sitemap_categories')->where('id', $parent_id)->first();
                if (empty($item)) {
                    $error = response()->json(['error' => 'Invalid parent_id - it doesn\'t exist', 'data' => $parent_id], 412, []);
                }
            }
        }
        
        if (is_null($error)) {            
            
            // Save this new category in our DB
            $new_sc = \App\Models\SitemapCategory::create([
                'name' => $valor,
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
     *     @OA\Response(response="200", description="New Sitemap cateogory updated"),
     *     @OA\Response(response="412", description="Precondition Failed")
     * )
     */
    public function update(Request $request, $id, $new_name) {
        $error = null;
        
        # Validations
        # 1 - $id (format and existant)
        # 2 - $new_name (format and existant)
        
        # 1 - $id (format and existant)
        $id = (urldecode($id) == '{id}') ? null : trim($id);
        if (!is_numeric($id)) {
            // check format
            $error = response()->json(['error' => 'Invalid id value', 'data' => $id], 412, []);
        }
        
        # 2 - $new_name
        $new_name = (urldecode($new_name) == '{new_name}') ? null : trim(urldecode($new_name));
        if (is_null($error) && is_null($new_name)) {
            $error = response()->json(['error' => 'Name can not be empty', 'data' => $valor], 412, []);        
        } 
        if (is_null($error)) {
            // search for that name - similar category check
            $item = \DB::table('sitemap_categories')->where('name', $new_name)->first();
            
            if (!empty($item)) {
                $error = response()->json(['error' => 'Category Already exists', 'data' => $new_name], 412, []);        
            }
        }
        
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
        
        
        # 1 - $id (format and existant)
        $id = (urldecode($id) == '{id}') ? null : trim($id);
        if (is_null($id)) {
            $error = response()->json(['error' => 'ID can not be empty', 'data' => $id], 412, []);        
        } 
        if (is_null($error) && !is_numeric($id)) {
            // check format
            $error = response()->json(['error' => 'Invalid ID value', 'data' => $id], 412, []);
        } 
        if (is_null($error)) {
            // check existant category
            $item = \DB::table('sitemap_categories')->where('id', $id)->first();
            if (empty($item)) {
                $error = response()->json(['error' => 'Invalid ID - it doesn\'t exist', 'data' => $id], 412, []);
            }
        }
            
        # 2 - $id (existant relationships)
        if (is_null($error)) {
            // check related categories
            $item = \DB::table('sitemap_categories')->where('parent_id', $id)->first();
            if (!empty($item)) {
                $error = response()->json(['error' => 'There are related categories linked to it - can\'t delete', 'data' => $item], 412, []);
            }
        }
            
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

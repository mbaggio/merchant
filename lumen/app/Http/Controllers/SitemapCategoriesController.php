<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class SitemapCategoriesController extends Controller
{
    public function create(Request $request, $name, $parent_id = null) {
        $error = null;
        
        # Validations
        # 1 - $name (format and existant)
        # 2 - $parent_id (format and existant)
        
        # 1 - $name
        $valor = trim(urldecode($name));
        if (!empty($valor)) {
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

            return response()->json(['success' => 'Item addedd', 'data' => $new_sc], 201);    

        } else {
            
            return $error;
            
        }
    }
}

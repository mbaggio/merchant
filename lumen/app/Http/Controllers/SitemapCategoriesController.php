<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class SitemapCategoriesController extends Controller
{
    public function create(Request $request, $name) {
        
        $valor = stripslashes($name);
        
        $valor = str_ireplace("SELECT","",$valor);
        $valor = str_ireplace("COPY","",$valor);
        $valor = str_ireplace("DELETE","",$valor);
        $valor = str_ireplace("DROP","",$valor);
        $valor = str_ireplace("DUMP","",$valor);
        $valor = str_ireplace(" OR ","",$valor);
        $valor = str_ireplace("%","",$valor);
        $valor = str_ireplace("LIKE","",$valor);
        $valor = str_ireplace("--","",$valor);
        $valor = str_ireplace("^","",$valor);
        $valor = str_ireplace("[","",$valor);
        $valor = str_ireplace("]","",$valor);
        $valor = str_ireplace("\\","",$valor);
        $valor = str_ireplace("!","",$valor);
        $valor = str_ireplace("¡","",$valor);
        $valor = str_ireplace("?","",$valor);
        $valor = str_ireplace("=","",$valor);
        $valor = str_ireplace("&","",$valor);
        
        $valor = trim($valor);
        
        if (!empty($valor)) {
            
            // search for it
            $item = \DB::table('sitemap_categories')->where('name', $valor)->first();
            
            if (empty($item)) {
                $new_sc = new \App\Models\SitemapCategory();
                $new_sc->name = $valor;
                $new_sc->save();

                return response()->json(['success' => 'Item addedd'], 201);    
            } else {
                return response()->json(['error' => 'Already exists', 'data' => json_encode($valor)], 412, []);        
            }
            
        } else {
            return response()->json(['error' => 'Invalid name', 'data' => json_encode($valor)], 412, []);    
        }
        
    }
}

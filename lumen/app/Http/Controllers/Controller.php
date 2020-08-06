<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @OA\Info(
     *   title="Mario Baggio test",
     *   version="1.0",
     *   @OA\Contact(
     *     email="mario.baggio@gmail.com",
     *     name="Mario Baggio"
     *   )
     * )
     */
    
    
    /**
     * @OA\Get(
     *     path="/",
     *     description="Home page",
     *     @OA\Response(response="default", description="Welcome page")
     * )
     */
    public function getThings() {
        
    }
}

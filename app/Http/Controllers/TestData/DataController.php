<?php

namespace App\Http\Controllers\TestData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * Function to show that this endpoint is open to an  one
     */
    public function open() 
    {
        $data = "This data is open and can be accessed without the client being authenticated";
        return response()->json(compact('data'),200);

    }

    /**
     * Function to show that this end point is closed unless if one is logged in
     */
    public function closed() 
    {
        $data = "Only authorized users can see this";
        return response()->json(compact('data'),200);
    }
}

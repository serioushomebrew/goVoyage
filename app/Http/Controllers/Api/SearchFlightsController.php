<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchFlightsController extends Controller
{
    public function search(Request $request)
    {
        return response()->json([
            'error' => 'Dummy feed',
        ]);
    }
}

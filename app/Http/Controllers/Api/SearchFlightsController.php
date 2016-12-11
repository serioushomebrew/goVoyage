<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use \Carbon\Carbon;

use App\GoVoyage\Library\Flights;

class SearchFlightsController extends Controller
{
    public function search(Request $request)
    {
        $response = Flights::search(
            $request->start ? Carbon::createFromFormat('d-m-Y', $request->start) : null,
            $request->end ? Carbon::createFromFormat('d-m-Y', $request->end) : null,
            $request->budget ?? null,
            $request->passengers ?? null,
            $request->temperature ?? null
        );

        // dd($response->toArray());



        return response()->json($response);
    }
}

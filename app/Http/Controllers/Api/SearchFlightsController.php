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
            Carbon::createFromFormat('d-m-Y', $request->start ?? '12-12-2016'),
            Carbon::createFromFormat('d-m-Y', $request->end ?? '01-01-2017'),
            $request->budget ?? 700,
            $request->passengers ?? 1,
            $request->temperature ?? 35
        );

        return response()->json($response);
    }
}

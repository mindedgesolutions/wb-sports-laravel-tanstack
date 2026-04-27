<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpWbsCouncilDesignation;
use Illuminate\Http\Response;

class WbsCouncilDesignationController extends Controller
{
    public function index()
    {
        $data = SpWbsCouncilDesignation::all();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }
}

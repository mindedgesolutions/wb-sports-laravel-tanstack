<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DistrictBlockOfficeRequest;
use App\Models\DistrictBlockOffice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DistrictBlockOfficeController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = DistrictBlockOffice::where('organisation', 'services')
            ->join('districts', 'districts.id', '=', 'district_block_offices.district_id')
            ->select('district_block_offices.*', 'districts.name as district_name')
            ->searchOffice($search)
            ->orderBy('districts.name')
            ->orderBy('district_block_offices.name')
            ->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total(),
            ]
        ], Response::HTTP_OK);
    }

    // --------------------------------

    public function store(DistrictBlockOfficeRequest $request)
    {
        DistrictBlockOffice::create([
            'district_id' => $request->districtId,
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address' => trim($request->address),
            'landline_no' => $request->landline ?? null,
            'mobile_1' => $request->mobile_1 ?? null,
            'mobile_2' => $request->mobile_2 ?? null,
            'email' => $request->email ?? null,
            'officer_name' => trim($request->officerName) ?? null,
            'officer_designation' => trim($request->officerDesignation),
            'officer_mobile' => $request->officerMobile ?? null,
            'added_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // --------------------------------

    public function update(DistrictBlockOfficeRequest $request, string $id)
    {
        DistrictBlockOffice::where('id', $id)->update([
            'district_id' => $request->districtId,
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address' => trim($request->address),
            'landline_no' => $request->landline ?? null,
            'mobile_1' => $request->mobile_1 ?? null,
            'mobile_2' => $request->mobile_2 ?? null,
            'email' => $request->email ?? null,
            'officer_name' => trim($request->officerName) ?? null,
            'officer_designation' => trim($request->officerDesignation),
            'officer_mobile' => $request->officerMobile ?? null,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------

    public function destroy(string $id)
    {
        DistrictBlockOffice::where('id', $id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------

    public function toggle(Request $request, string $id)
    {
        DistrictBlockOffice::where('id', $id)->update([
            'is_active' => $request->checked,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

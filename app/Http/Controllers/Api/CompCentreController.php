<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompCentreRequest;
use App\Models\CompCenter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CompCentreController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = CompCenter::select('comp_centers.*')->with('district')
            ->join('districts', 'districts.id', '=', 'comp_centers.district_id')
            ->searchCentre($search)
            ->orderBy('districts.name', 'asc')
            ->orderBy('comp_centers.id', 'asc')
            ->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total()
            ]
        ], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function store(CompCentreRequest $request)
    {
        CompCenter::create([
            'district_id' => (int)$request->districtId,
            'yctc_name' => trim($request->name),
            'yctc_code' => trim($request->code) ?? null,
            'center_category' => $request->category ?? null,
            'address_line_1' => trim($request->addressLine1) ?? null,
            'address_line_2' => trim($request->addressLine2) ?? null,
            'address_line_3' => trim($request->addressLine3) ?? null,
            'city' => trim($request->city) ?? null,
            'pincode' => $request->pincode ?? null,
            'center_incharge_name' => trim($request->inchargeName) ?? null,
            'center_incharge_mobile' => $request->inchargeMobile ?? null,
            'center_incharge_email' => $request->inchargeEmail ?? null,
            'center_owner_name' => trim($request->ownerName) ?? null,
            'center_owner_mobile' => $request->ownerMobile ?? null,
            'added_by' => Auth::id(),
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['message' => 'Center added successfully'], Response::HTTP_CREATED);
    }

    // --------------------------------------------

    public function update(CompCentreRequest $request, string $id)
    {
        CompCenter::where('id', $id)->update([
            'district_id' => (int)$request->districtId,
            'yctc_name' => $request->name,
            'yctc_code' => trim($request->code) ?? null,
            'center_category' => $request->category ?? null,
            'address_line_1' => trim($request->addressLine1) ?? null,
            'address_line_2' => trim($request->addressLine2) ?? null,
            'address_line_3' => trim($request->addressLine3) ?? null,
            'city' => trim($request->city) ?? null,
            'pincode' => $request->pincode ?? null,
            'center_incharge_name' => trim($request->inchargeName) ?? null,
            'center_incharge_mobile' => $request->inchargeMobile ?? null,
            'center_incharge_email' => $request->inchargeEmail ?? null,
            'center_owner_name' => trim($request->ownerName) ?? null,
            'center_owner_mobile' => $request->ownerMobile ?? null,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['message' => 'Center updated successfully'], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function destroy(string $id)
    {
        CompCenter::where('id', $id)->delete();

        return response()->json(['message' => 'Center deleted successfully'], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function toggle(Request $request, String $id)
    {
        CompCenter::where('id', $id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'Status updated successfully'], Response::HTTP_OK);
    }
}

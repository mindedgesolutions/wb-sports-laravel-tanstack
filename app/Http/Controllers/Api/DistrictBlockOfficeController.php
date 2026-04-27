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
        $data = DistrictBlockOffice::where('organisation', 'services')
            ->join('districts', 'districts.id', '=', 'district_block_offices.district_id')
            ->select('district_block_offices.*', 'districts.name as district_name')
            ->when(request()->query('dist'), function ($query) {
                return $query->where('district_block_offices.district_id', request()->query('dist'));
            })
            ->when(request()->query('s'), function ($query) {
                return $query->where('district_block_offices.name', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('district_block_offices.address', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('district_block_offices.landline_no', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('district_block_offices.email', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('district_block_offices.officer_name', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('district_block_offices.officer_designation', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('district_block_offices.officer_mobile', 'like', '%' . request()->query('s') . '%');
            })->orderBy('districts.name', 'asc')
            ->orderBy('district_block_offices.name', 'asc')
            ->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function store(DistrictBlockOfficeRequest $request)
    {
        DistrictBlockOffice::create([
            'district_id' => $request->district,
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
            'district_id' => $request->district,
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

    public function activate(Request $request, string $id)
    {
        DistrictBlockOffice::where('id', $id)->update([
            'is_active' => $request->is_active,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

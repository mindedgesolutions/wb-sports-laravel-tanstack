<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompCentreRequest;
use App\Models\CompCenter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompCentreController extends Controller
{
    public function index()
    {
        $data = CompCenter::select('comp_centers.*')->with('district')
            ->join('districts', 'districts.id', '=', 'comp_centers.district_id')
            ->when(request()->query('dist'), function ($query) {
                return $query->where('comp_centers.district_id', request()->query('dist'));
            })
            ->when(request()->query('cat'), function ($query) {
                return $query->where('comp_centers.center_category', request()->query('cat'));
            })
            ->when(request()->query('s'), function ($query) {
                return $query->where('comp_centers.yctc_name', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.yctc_code', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.address_line_1', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.address_line_2', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.address_line_3', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.city', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.pincode', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.center_incharge_name', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.center_incharge_mobile', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.center_incharge_email', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.center_owner_name', 'like', '%' . request()->query('s') . '%')
                    ->orWhere('comp_centers.center_owner_mobile', 'like', '%' . request()->query('s') . '%');
            })
            ->orderBy('districts.name', 'asc')
            ->orderBy('comp_centers.id', 'asc')
            ->paginate(10);

        return response()->json($data, Response::HTTP_OK);
    }

    // --------------------------------------------

    public function store(CompCentreRequest $request)
    {
        CompCenter::create([
            'district_id' => (int)$request->district,
            'yctc_name' => trim($request->yctcName),
            'yctc_code' => trim($request->yctcCode) ?? null,
            'center_category' => $request->centreCategory ?? null,
            'address_line_1' => trim($request->address1) ?? null,
            'address_line_2' => trim($request->address2) ?? null,
            'address_line_3' => trim($request->address3) ?? null,
            'city' => trim($request->city) ?? null,
            'pincode' => $request->pincode ?? null,
            'center_incharge_name' => trim($request->inchargeName) ?? null,
            'center_incharge_mobile' => $request->inchargeMobile ?? null,
            'center_incharge_email' => $request->inchargeEmail ?? null,
            'center_owner_name' => trim($request->ownerName) ?? null,
            'center_owner_mobile' => $request->ownerMobile ?? null,
            'added_by' => Auth::id(),
            'slug' => Str::slug($request->yctcName),
        ]);

        return response()->json(['message' => 'Center added successfully'], Response::HTTP_CREATED);
    }

    // --------------------------------------------

    public function update(CompCentreRequest $request, string $id)
    {
        CompCenter::where('id', $id)->update([
            'district_id' => (int)$request->district,
            'yctc_name' => $request->yctcName,
            'yctc_code' => trim($request->yctcCode) ?? null,
            'center_category' => $request->centreCategory ?? null,
            'address_line_1' => trim($request->address1) ?? null,
            'address_line_2' => trim($request->address2) ?? null,
            'address_line_3' => trim($request->address3) ?? null,
            'city' => trim($request->city) ?? null,
            'pincode' => $request->pincode ?? null,
            'center_incharge_name' => trim($request->inchargeName) ?? null,
            'center_incharge_mobile' => $request->inchargeMobile ?? null,
            'center_incharge_email' => $request->inchargeEmail ?? null,
            'center_owner_name' => trim($request->ownerName) ?? null,
            'center_owner_mobile' => $request->ownerMobile ?? null,
            'slug' => Str::slug($request->yctcName),
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

    public function activate(Request $request, $id)
    {
        CompCenter::where('id', $id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'Status updated successfully'], Response::HTTP_OK);
    }
}

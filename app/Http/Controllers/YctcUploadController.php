<?php

namespace App\Http\Controllers;

use App\Imports\YctcImport;
use App\Models\CompCenter;
use App\Models\YctcUploadTemp;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class YctcUploadController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function uploadTemp(Request $request)
    {
        Excel::import(new YctcImport, $request->file('file'));

        return response()->json([
            'message' => 'import successful.',
        ]);
    }

    public function transfer()
    {
        $all = YctcUploadTemp::orderBy('id')->get();
        $batch = [];

        foreach ($all as $data) {
            $batch[] = [
                'district_id' => $data->district ?? 25,
                'yctc_name' => $data->yctc_name,
                'yctc_code' => $data->yctc_code ?? null,
                'center_category' => $data->center_category ?? null,
                'address_line_1' => $data->address_line_1 ?? null,
                'address_line_2' => $data->address_line_2 ?? null,
                'address_line_3' => $data->address_line_3 ?? null,
                'city' => $data->city ?? null,
                'pincode' => $data->pincode ?? null,
                'center_incharge_name' => $data->center_incharge_name ?? null,
                'center_incharge_mobile' => $data->center_incharge_mobile ?? null,
                'center_incharge_email' => $data->center_incharge_email ?? null,
                'center_owner_name' => $data->center_owner_name ?? null,
                'center_owner_mobile' => $data->center_owner_mobile ?? null,
                'is_active' => true,
                'added_by' => 1,
                'slug' => Str::slug($data->yctc_name),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        CompCenter::insert($batch);

        return response()->json([
            'message' => 'Data transferred successfully.',
        ]);
    }
}

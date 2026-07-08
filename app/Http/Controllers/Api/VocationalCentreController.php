<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VocationalTrainingCentre;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VocationalCentreController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = VocationalTrainingCentre::from('vocational_training_centres as vc')
            ->join('districts as d', 'vc.district_id', '=', 'd.id')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('d.name', 'ilike', "%{$search}%")
                        ->orWhere('vc.name_of_centre', 'ilike', "%{$search}%")
                        ->orWhere('vc.address', 'ilike', "%{$search}%")
                        ->orWhere('vc.phone', 'ilike', "%{$search}%");
                });
            })
            ->select('d.name as district_name', 'vc.*')
            ->orderBy('d.name')
            ->orderBy('vc.name_of_centre')
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

    // -------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'district' => 'required',
            'name' => ['required', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                if (VocationalTrainingCentre::where('slug', $slug)->exists()) {
                    $fail('Centre exists');
                }
            }],
            'address' => ['required', 'max:255'],
            'phone' => 'nullable',
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute cannot be more than 255 characters'
        ], [
            'district' => 'district',
            'name' =>  'Centre name',
            'address'  => 'address',
            'phone' => 'phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        VocationalTrainingCentre::create([
            'district_id' => $request->district,
            'name_of_centre' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address'  => trim($request->address),
            'phone' => $request->phone ? trim($request->phone) : null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'district' => 'required',
            'name' => ['required', function ($attribute, $value, $fail) use ($id) {
                $slug = Str::slug($value);
                if (VocationalTrainingCentre::where('slug', $slug)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    $fail('Centre exists');
                }
            }],
            'address' => ['required', 'max:255'],
            'phone' => 'nullable',
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute cannot be more than 255 characters'
        ], [
            'district' => 'district',
            'name' =>  'Centre name',
            'address'  => 'address',
            'phone' => 'phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        VocationalTrainingCentre::whereId($id)->update([
            'district_id' => $request->district,
            'name_of_centre' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address'  => trim($request->address),
            'phone' => $request->phone ? trim($request->phone) : null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------

    public function destroy(string $id)
    {
        VocationalTrainingCentre::destroy($id);

        return response()->json(['message' => 'success'], Response::HTTP_NO_CONTENT);
    }

    // -------------------------------

    public function toggle(Request $request, string $id)
    {
        VocationalTrainingCentre::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

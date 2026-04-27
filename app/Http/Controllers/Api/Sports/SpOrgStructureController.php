<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpOrganisationStructure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SpOrgStructureController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpOrganisationStructure::when($search, function ($query, $search) {
            $query->where('designation', 'ILIKE', "%{$search}%");
        })->orderBy('show_order', 'asc')
            ->orderBy('id', 'desc')
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

    // ---------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'designation' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SpOrganisationStructure::where('designation', $inputSlug)->exists()) {
                    $fail('Designation exists');
                }
            }],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        SpOrganisationStructure::create([
            'designation' => $request->designation,
            'slug' => Str::slug($request->designation),
        ]);

        return response()->json('success', Response::HTTP_CREATED);
    }

    // ---------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'designation' => ['required', 'max:255', function ($attribute, $value, $fail) use ($id) {
                $inputSlug = Str::slug($value);
                if (SpOrganisationStructure::where('designation', $inputSlug)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    $fail('Designation exists');
                }
            }],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        SpOrganisationStructure::whereId($id)->update([
            'designation' => $request->designation,
            'slug' => Str::slug($request->designation),
        ]);

        return response()->json('success', Response::HTTP_OK);
    }

    // ---------------------------------------------

    public function destroy(string $id)
    {
        SpOrganisationStructure::whereId($id)->delete();

        return response()->json('success', Response::HTTP_OK);
    }

    // ---------------------------------------------

    public function toggle(Request $request, string $id)
    {
        SpOrganisationStructure::whereId($id)->update([
            'is_active' => $request->checked,
        ]);

        return response()->json('success', Response::HTTP_OK);
    }

    // ---------------------------------------------

    public function all()
    {
        $data = SpOrganisationStructure::where('is_active', true)
            ->orderBy('show_order', 'asc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ---------------------------------------------

    public function sort(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            SpOrganisationStructure::where('id', $value['id'])->update(['show_order' => $key]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

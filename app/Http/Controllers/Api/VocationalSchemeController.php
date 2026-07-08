<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VocationalTraining;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VocationalSchemeController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = VocationalTraining::when($search, function ($query, $search) {
            $query->where('content', 'ilike', "%{$search}%");
        })
            ->orderBy('show_order')
            ->orderBy('id')
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
            'scheme' => ['required', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                if (VocationalTraining::where('slug', $slug)->exists()) {
                    $fail('Scheme exists');
                }
            }],
        ], [
            '*.required' => ':Attribute is required',
        ], [
            'scheme' => 'Scheme content',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slug = Str::slug($request->scheme);

        VocationalTraining::create([
            'content' => $request->scheme,
            'slug' => $slug,
            'is_active' => true
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'scheme' => ['required', function ($attribute, $value, $fail) use ($id) {
                $slug = Str::slug($value);
                if (VocationalTraining::where('slug', $slug)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    $fail('Scheme exists');
                }
            }],
        ], [
            '*.required' => ':Attribute is required',
        ], [
            'scheme' => 'Scheme content',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slug = Str::slug($request->scheme);

        VocationalTraining::whereId($id)->update([
            'content' => $request->scheme,
            'slug' => $slug,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------

    public function destroy(string $id)
    {
        VocationalTraining::destroy($id);

        return response()->json(['message' => 'success'], Response::HTTP_NO_CONTENT);
    }

    // -------------------------------

    public function all()
    {
        $data = VocationalTraining::orderBy('show_order')->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -------------------------------

    public function sort(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            VocationalTraining::where('id', $value['id'])->update(['show_order' => $key]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------

    public function toggle(Request $request, string $id)
    {
        VocationalTraining::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

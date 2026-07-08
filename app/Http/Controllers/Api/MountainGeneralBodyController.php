<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MountainGeneralBody;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MountainGeneralBodyController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = MountainGeneralBody::when($search, function ($query, $search) {
            $query->where('designation', 'ilike', "%{$search}%")
                ->orWhere('name', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%")
                ->orWhere('organisation', 'ilike', "%{$search}%");
        })
            ->orderBy('show_order')
            ->orderBy('name')
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
            'designation' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255']
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute cannot be more than 255 characters'
        ], [
            'description' => 'description',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slug = Str::slug($request->name);

        MountainGeneralBody::create([
            'designation' => trim($request->designation) ?? null,
            'name' => trim($request->name),
            'description' => trim($request->description),
            'organisation' => 'services',
            'added_by' => Auth::id(),
            'slug' => $slug,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'designation' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255']
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute cannot be more than 255 characters'
        ], [
            'description' => 'description',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slug = Str::slug($request->name);

        MountainGeneralBody::whereId($id)->update([
            'designation' => trim($request->designation) ?? null,
            'name' => trim($request->name),
            'description' => trim($request->description),
            'organisation' => 'services',
            'slug' => $slug,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------

    public function destroy(string $id)
    {
        MountainGeneralBody::destroy($id);

        return response()->json(['message' => 'success'], Response::HTTP_NO_CONTENT);
    }

    // -------------------------------

    public function all()
    {
        $data = MountainGeneralBody::orderBy('show_order')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -------------------------------

    public function sort(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            MountainGeneralBody::where('id', $value['id'])->update(['show_order' => $key]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------

    public function toggle(Request $request, string $id)
    {
        MountainGeneralBody::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

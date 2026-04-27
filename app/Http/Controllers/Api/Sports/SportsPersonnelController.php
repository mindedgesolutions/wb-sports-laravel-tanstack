<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpSportsPersonnel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SportsPersonnelController extends Controller
{
    protected $sports;

    public function __construct()
    {
        $this->sports = [
            'football',
            'cricket',
            'hockey',
            'lawn-tennis',
            'swimming',
            'table-tennis',
            'archery',
            'body-building',
            'chess',
            'boxing',
            'athletics',
            'gymnastic'
        ];
    }

    // ---------------------------

    public function index()
    {
        $search = request()->query('search');

        $data = SpSportsPersonnel::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('sport', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
            });
        })
            ->orderBy('sport')
            ->orderBy('name')
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

    // ---------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sport' => 'required|in:' . implode(',', $this->sports),
            'name' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $slug = Str::slug($value);
                    $check = SpSportsPersonnel::where('slug', $slug)
                        ->where('sport', $request->sport)
                        ->exists();
                    if ($check) {
                        return $fail('Name exists in the same sport.');
                    }
                },
            ],
            'address' => 'nullable|string|max:255',
            'dob' => 'nullable|before:today',
            'contactOne' => 'nullable|string|max:255',
            'contactTwo' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        SpSportsPersonnel::create([
            'sport' => $request->sport,
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address' => $request->address ? trim($request->address) : null,
            'dob' => $request->dob ? $request->dob : null,
            'contact_1' => $request->contactOne && $request->contactOne !== 0 ? $request->contactOne : null,
            'contact_2' => $request->contactTwo && $request->contactTwo !== 0 ? $request->contactTwo : null,
            'added_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // ---------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'sport' => 'required|in:' . implode(',', $this->sports),
            'name' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $id) {
                    $slug = Str::slug($value);
                    $check = SpSportsPersonnel::where('slug', $slug)
                        ->where('sport', $request->sport)
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($check) {
                        return $fail('Name exists in the same sport.');
                    }
                },
            ],
            'address' => 'nullable|string|max:255',
            'dob' => 'nullable|before:today',
            'contactOne' => 'nullable|string|max:255',
            'contactTwo' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        SpSportsPersonnel::whereId($id)->update([
            'sport' => $request->sport,
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address' => $request->address ? trim($request->address) : null,
            'dob' => $request->dob ? $request->dob : null,
            'contact_1' => $request->contactOne && $request->contactOne !== 0 ? $request->contactOne : null,
            'contact_2' => $request->contactTwo && $request->contactTwo !== 0 ? $request->contactTwo : null,
            'added_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // ---------------------------

    public function destroy(string $id)
    {
        SpSportsPersonnel::whereId($id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // ---------------------------

    public function spPersonnelAll()
    {
        $data = SpSportsPersonnel::orderBy('show_order', 'asc')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ---------------------------

    public function spPersonnelSetOrder(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            SpSportsPersonnel::where('id', $value['id'])->update(['show_order' => $key]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // ---------------------------

    public function toggle(Request $request, $id)
    {
        SpSportsPersonnel::whereId($id)->update([
            'is_active' => $request->checked,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

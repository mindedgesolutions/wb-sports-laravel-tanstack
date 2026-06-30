<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganisationChart;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrganisationChartController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = OrganisationChart::when($search, function ($query, $search) {
            $query->where('name', 'ILIKE', "%{$search}%")
                ->orWhere('designation', 'ILIKE', "%{$search}%")
                ->orWhere('rank', 'ILIKE', "%{$search}%");
        })
            ->orderBy('show_order')
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

    // -------------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) {
                    $slug = Str::slug($value);
                    if (OrganisationChart::where('slug', $slug)->exists()) {
                        $fail('Name exists');
                    }
                }
            ],
            'designation' => 'required|max:255',
            'department' => 'required|max:255',
            'message' => 'nullable|max:500',
            'newImg' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:10240'
            ],
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute must not exceed :max characters',
            'newImg.image' => 'Profile image must be a valid image file',
            'newImg.mimes' => 'Profile image must be of type jpeg, png, jpg, or gif',
            'newImg.max' => 'Profile image size must not exceed 10 MB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $filePath = '';

        if ($request->hasFile('newImg') && $request->file('newImg')->getSize() > 0) {
            $file = $request->file('newImg');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/org-chart';

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');
        }

        OrganisationChart::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'designation' => trim($request->designation),
            'department' => $request->department,
            'rank' => $request->rank ? trim($request->rank) : null,
            'message' => $request->message ? trim($request->message) : null,
            'image_path' => $request->hasFile('newImg') ? Storage::url($filePath) : null,
            'added_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    $slug = Str::slug($value);
                    if (OrganisationChart::where('slug', $slug)
                        ->where('id', '!=', $id)
                        ->exists()
                    ) {
                        $fail('Name exists');
                    }
                }
            ],
            'designation' => 'required|max:255',
            'message' => 'nullable',
            'newImg' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:10240'
            ],
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute must not exceed :max characters',
            'newImg.image' => 'Profile image must be a valid image file',
            'newImg.mimes' => 'Profile image must be of type jpeg, png, jpg, or gif',
            'newImg.max' => 'Profile image size must not exceed 10 MB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = OrganisationChart::findOrFail($id);

        $filePath = '';

        if ($request->hasFile('newImg') && $request->file('newImg')->getSize() > 0) {
            $file = $request->file('newImg');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/org-chart';

            if ($data) {
                $deletePath = str_replace('/storage', '', $data->image_path);

                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');
        }

        OrganisationChart::whereId($id)->update([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'designation' => trim($request->designation),
            'department' => $request->department,
            'rank' => $request->rank ? trim($request->rank) : null,
            'message' => $request->message ? trim($request->message) : null,
            'image_path' => $request->hasFile('newImg') ? Storage::url($filePath) : $data->image_path ?? null,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------------

    public function destroy(string $id)
    {
        $data = OrganisationChart::findOrFail($id);

        if ($data->image_path) {
            $deletePath = str_replace('/storage', '', $data->image_path);

            if (Storage::disk('public')->exists($deletePath)) {
                Storage::disk('public')->delete($deletePath);
            }
        }
        OrganisationChart::where('id', $id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------------

    public function toggle(Request $request, string $id)
    {
        OrganisationChart::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------------

    public function all()
    {
        $data = OrganisationChart::where('is_active', true)
            ->orderBy('show_order', 'asc')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $data]);
    }

    // -------------------------------------------------

    public function sort(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            OrganisationChart::where('id', $value['id'])->update(['show_order' => $key]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

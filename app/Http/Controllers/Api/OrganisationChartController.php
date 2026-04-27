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
        $data = OrganisationChart::orderBy('show_order')->paginate(10);

        return response()->json(['data' => $data]);
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
            'message' => 'nullable',
            'profileImg' => 'nullable|array',
            'profileImg.*' => 'image|mimes:jpeg,png,jpg,gif|max:100',
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute must not exceed :max characters',
            'profileImg.image' => 'Profile image must be a valid image file',
            'profileImg.mimes' => 'Profile image must be of type jpeg, png, jpg, or gif',
            'profileImg.max' => 'Profile image size must not exceed 100KB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->hasFile('profileImg') && $request->file('profileImg')[0]->getSize() > 0) {
            $file = $request->file('profileImg')[0];
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
            'rank' => $request->rank ? trim($request->rank) : null,
            'department' => $request->department ? $request->department : null,
            'message' => $request->message ? trim($request->message) : null,
            'image_path' => $request->hasFile('profileImg') ? Storage::url($filePath) : null,
            'added_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------------------------

    public function updateMember(Request $request, string $id)
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
            'profileImg' => 'nullable|array',
            'profileImg.*' => 'image|mimes:jpeg,png,jpg,gif|max:100',
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute must not exceed :max characters',
            'profileImg.image' => 'Profile image must be a valid image file',
            'profileImg.mimes' => 'Profile image must be of type jpeg, png, jpg, or gif',
            'profileImg.max' => 'Profile image size must not exceed 100KB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = OrganisationChart::findOrFail($id);

        if ($request->hasFile('profileImg') && $request->file('profileImg')[0]->getSize() > 0) {
            $file = $request->file('profileImg')[0];
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
            'rank' => $request->rank ? trim($request->rank) : null,
            'department' => $request->department ? $request->department : null,
            'message' => $request->message ? trim($request->message) : null,
            'image_path' => $request->hasFile('profileImg') ? Storage::url($filePath) : $data->image_path ?? null,
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

    public function activate(Request $request, string $id)
    {
        OrganisationChart::whereId($id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------------

    public function orgChartAll()
    {
        $data = OrganisationChart::where('is_active', true)
            ->orderBy('show_order', 'asc')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $data]);
    }

    // -------------------------------------------------

    public function orgChartSetOrder(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            OrganisationChart::where('id', $value['id'])->update(['show_order' => $key]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

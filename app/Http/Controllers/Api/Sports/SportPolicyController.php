<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpSportPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SportPolicyController extends Controller
{
    public function index()
    {
        $data = SpSportPolicy::orderBy('name')->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SpSportPolicy::where('slug', $inputSlug)->exists()) {
                    $fail('Policy exists');
                }
            }],
            'file' => 'required|array',
            'file.*' => 'required|file|mimes:pdf|max:5120',
        ], [
            '*.required' => ':Attribute is required.',
            '*.max' => 'File size cannot exceed 5MB.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            DB::beginTransaction();

            $data = SpSportPolicy::create([
                'name' => trim($request->name),
                'slug' => Str::slug($request->name),
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file')[0];
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/sports-policies';
                $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                $filePath = Storage::disk('public')->putFileAs($directory, $file, $filename);

                SpSportPolicy::whereId($data->id)->update([
                    'file_path' => Storage::url($filePath),
                    'file_name' => $fileOriginalName,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // --------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) use ($id) {
                $inputSlug = Str::slug($value);
                if (SpSportPolicy::where('slug', $inputSlug)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    $fail('Policy exists');
                }
            }],
            'file' => 'nullable|array',
            'file.*' => 'nullable|file|mimes:pdf|max:5120',
        ], [
            '*.required' => ':Attribute is required.',
            '*.max' => 'File size cannot exceed 5MB.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            DB::beginTransaction();

            $data = SpSportPolicy::findOrFail($id);

            SpSportPolicy::whereId($id)->update([
                'name' => trim($request->name),
                'slug' => Str::slug($request->name),
            ]);

            if ($request->hasFile('file') && $request->file('file')[0]->getSize() > 0) {
                $file = $request->file('file')[0];
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/sports-policies';
                $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                if ($data->file_path) {
                    $deletePath = str_replace('/storage', '', $data->file_path);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                $filePath = Storage::disk('public')->putFileAs($directory, $file, $filename);

                SpSportPolicy::whereId($data->id)->update([
                    'file_path' => Storage::url($filePath),
                    'file_name' => $fileOriginalName,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // --------------------------------------------
    public function destroy(string $id)
    {
        $data = SpSportPolicy::whereId($id)->first();

        $filePath = $data->file_path ? str_replace('/storage', '', $data->file_path) : null;

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        SpSportPolicy::where('id', $id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function activate(Request $request, string $id)
    {
        SpSportPolicy::whereId($id)->update([
            'is_active' => $request->is_active,
        ]);
        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

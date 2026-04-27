<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpAward;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AwardsController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpAward::when($search, function ($query, $search) {
            $query->where('name', 'ILIKE', "%{$search}%");
        })
            ->orderBy('id', 'desc')
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

    // ------------------------------------------

    public function store(Request $request)
    {
        if (!$request->hasFile('newFile')) {
            $data['newFile'] = null;
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SpAward::where('slug', $inputSlug)->exists()) {
                    $fail('Award exists');
                }
            }],
            'newFile' => 'required|file|max:102400',
        ], [
            '*.required' => ':Attribute is required.',
            'newFile.max' => 'The :attribute may not be greater than 100MB.',
        ], [
            'name' => 'name',
            'newFile' => 'file',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            DB::beginTransaction();

            $data = SpAward::create([
                'name' => trim($request->name),
                'slug' => Str::slug($request->name),
            ]);

            if ($request->hasFile('newFile')) {
                $file = $request->file('newFile');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/awards';
                $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                $filePath = Storage::disk('public')->putFileAs($directory, $file, $filename);

                SpAward::whereId($data->id)->update([
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

    // ------------------------------------------

    public function update(Request $request, $id)
    {
        if (!$request->hasFile('newFile')) {
            $data['newFile'] = null;
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:255', function ($attribute, $value, $fail) use ($id) {
                $inputSlug = Str::slug($value);
                if (SpAward::where('slug', $inputSlug)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    $fail('Award exists');
                }
            }],
            'newFile' => 'required|file|max:102400',
        ], [
            '*.required' => ':Attribute is required.',
            'newFile.max' => 'The :attribute may not be greater than 100MB.',
        ], [
            'name' => 'name',
            'newFile' => 'file',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            DB::beginTransaction();

            $prev = SpAward::whereId($id)->first();

            $data = SpAward::whereId($id)->update([
                'name' => trim($request->name),
                'slug' => Str::slug($request->name),
            ]);

            if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
                $file = $request->file('newFile');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/awards';
                $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                if ($prev) {
                    $deletePath = str_replace('/storage', '', $prev->file_path);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                $filePath = $file->storeAs($directory, $filename, 'public');

                SpAward::whereId($id)->update([
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

    // ------------------------------------------

    public function destroy(string $id)
    {
        $data = SpAward::whereId($id)->first();

        try {
            DB::beginTransaction();

            $filePath = $data->file_path ? str_replace('/storage', '', $data->file_path) : null;

            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            SpAward::where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'deleted'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while processing your request.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------

    public function toggle(Request $request, $id)
    {
        SpAward::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'updated'], Response::HTTP_OK);
    }
}

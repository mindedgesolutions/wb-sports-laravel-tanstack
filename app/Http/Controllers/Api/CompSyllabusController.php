<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompSyllabus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CompSyllabusController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = CompSyllabus::where('organisation', 'services')
            ->when($search, function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%");
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

    // ----------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabusName' => 'required',
            'newFile' => 'required|mimes:pdf|max:5120'
        ], [
            '*.required' => ':Attribute is required',
            'newFile.mimes' => ':Attribute must be a pdf',
            'newFile.max' => ':Attribute must be less than 5MB',
        ], [
            'syllabusName' => 'Syllabus name',
            'newFile' => 'Attachment',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        try {
            DB::beginTransaction();

            $slug = Str::slug($request->syllabusName);
            $check = CompSyllabus::where('slug', $slug)->first();
            if ($check) {
                return response()->json(['errors' => ['syllabusName' => ['Syllabus name already exists']]], Response::HTTP_BAD_REQUEST);
            }

            $data = CompSyllabus::create([
                'name' => trim($request->syllabusName),
                'slug' => $slug,
                'organisation' => 'services',
                'added_by' => Auth::id(),
                'file_path' => ''
            ]);

            if ($request->hasFile('newFile')) {
                $file = $request->file('newFile');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/services/syllabus';
                $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                CompSyllabus::whereId($data->id)->update([
                    'file_path' => Storage::url($filePath),
                    'file_name' => $fileOriginalName
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Syllabus added successfully'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ----------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'syllabusName' => 'required',
            'newFile' => [
                'nullable',
                'mimes:pdf',
                'max:5120'
            ],
        ], [
            '*.required' => ':Attribute is required',
            'newFile.mimes' => ':Attribute must be a pdf',
            'newFile.max' => ':Attribute must be less than 1MB',
        ], [
            'syllabusName' => 'Syllabus name',
            'newFile' => 'Attachment',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $data = CompSyllabus::findOrFail($id);

        $slug = Str::slug($request->syllabusName);
        $check = CompSyllabus::where('slug', $slug)->where('id', '!=', $id)->first();

        if ($check) {
            return response()->json(['errors' => ['syllabusName' => ['Syllabus name already exists']]], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            CompSyllabus::where('id', $id)->update([
                'name' => trim($request->syllabusName),
                'slug' => $slug,
            ]);

            if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
                $file = $request->file('newFile');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/services/syllabus';
                $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                if ($data) {
                    $deletePath = str_replace('/storage', '', $data->file_path);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                CompSyllabus::whereId($id)->update([
                    'file_path' => Storage::url($filePath),
                    'file_name' => $fileOriginalName,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ----------------------------------------------

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $data = CompSyllabus::findOrFail($id);
            $filePath = str_replace('/storage', '', $data->file_path);

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            CompSyllabus::where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ----------------------------------------------

    public function toggle(Request $request, string $id)
    {
        CompSyllabus::where('id', $id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

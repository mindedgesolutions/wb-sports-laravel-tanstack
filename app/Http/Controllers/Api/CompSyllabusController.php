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
        $syllabus = CompSyllabus::where('organisation', 'services')->orderBy('id', 'desc')->paginate(10);

        return response()->json(['data' => $syllabus], Response::HTTP_OK);
    }

    // ----------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabusName' => 'required',
            'file' => 'required|array',
            'file.*' => 'mimes:pdf|max:1024'
        ], [
            '*.required' => ':Attribute is required',
            'file.mimes' => ':Attribute must be a pdf',
            'file.max' => ':Attribute must be less than 1MB',
        ], [
            'syllabusName' => 'Syllabus name',
            'file' => 'Attachment',
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

            if ($request->hasFile('file')) {
                $file = $request->file('file')[0];
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/services/syllabus';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');
            }
            CompSyllabus::create([
                'name' => trim($request->syllabusName),
                'slug' => $slug,
                'file_path' => Storage::url($filePath),
                'organisation' => 'services',
                'added_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json(['message' => 'Syllabus added successfully'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ----------------------------------------------

    public function syllabusUpdate(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'syllabusName' => 'required',
            'file' => ['nullable', 'array'],
            'file.*' => 'mimes:pdf|max:1024'
        ], [
            '*.required' => ':Attribute is required',
            'file.mimes' => ':Attribute must be a pdf',
            'file.max' => ':Attribute must be less than 1MB',
        ], [
            'syllabusName' => 'Syllabus name',
            'file' => 'Attachment',
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

            $filePath = '';

            if ($request->hasFile('file') && $request->file('file')[0]->getSize() > 0) {
                $file = $request->file('file')[0];
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/services/syllabus';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                if ($data) {
                    $deletePath = str_replace('/storage', '', $data->image_path);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                $filePath = $file->storeAs($directory, $filename, 'public');
            }

            CompSyllabus::where('id', $id)->update([
                'name' => trim($request->syllabusName),
                'slug' => $slug,
                'file_path' => $request->hasFile('file') ? Storage::url($filePath) : $data->file_path,
            ]);

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

    public function activate(Request $request, string $id)
    {
        CompSyllabus::where('id', $id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

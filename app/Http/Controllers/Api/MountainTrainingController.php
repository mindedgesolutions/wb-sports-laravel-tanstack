<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MountainTrainingRequest;
use App\Models\MountainTraining;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MountainTrainingController extends Controller
{
    public function index()
    {
        $data = MountainTraining::orderBy('id', 'desc')->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ---------------------------------------------

    public function store(MountainTrainingRequest $request)
    {
        $data = MountainTraining::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'courses_count' => $request->courseNo,
            'duration' => $request->duration,
            'age_group_start' => $request->groupStart,
            'age_group_end' => $request->groupEnd,
            'course_fee' => $request->courseFee ?? null,
            'remarks' => $request->remarks ? trim($request->remarks) : null,
        ]);

        if ($request->file('file') && $request->file('file')[0]->getSize() > 0) {
            $file = $request->file('file')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/mountain-courses';
            $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');
            MountainTraining::whereId($data->id)->update([
                'file_path' => Storage::url($filePath),
                'file_name' => $fileOriginalName,
            ]);
        }
        return response()->json(['data' => 'success'], Response::HTTP_CREATED);
    }

    // ---------------------------------------------

    public function update(MountainTrainingRequest $request, $id)
    {
        $data = MountainTraining::findOrFail($id);

        MountainTraining::whereId($id)->update([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'courses_count' => $request->courseNo,
            'duration' => $request->duration,
            'age_group_start' => $request->groupStart,
            'age_group_end' => $request->groupEnd,
            'course_fee' => $request->courseFee ?? null,
            'remarks' => $request->remarks ? trim($request->remarks) : null,
        ]);

        if ($request->file('file') && $request->file('file')[0]->getSize() > 0) {
            $file = $request->file('file')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/mountain-courses';
            $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if ($data->file_path) {
                $deletePath = str_replace('/storage', '', $data->file_path);

                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');
            MountainTraining::whereId($data->id)->update([
                'file_path' => Storage::url($filePath),
                'file_name' => $fileOriginalName,
            ]);
        }

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // ---------------------------------------------

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $data = MountainTraining::findOrFail($id);
            $filePath = $data->file_path ? str_replace('/storage', '', $data->file_path) : null;

            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            MountainTraining::where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'deleted'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while processing your request.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ---------------------------------------------

    public function activate(Request $request, $id)
    {
        MountainTraining::where('id', $id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

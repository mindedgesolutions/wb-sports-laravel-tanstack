<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MountainTrainingRequest;
use App\Models\MountainTraining;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MountainCourseController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = MountainTraining::when($search, function ($query, $search) {
            $query->where('name', 'ilike', "%{$search}%");
        })
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

    public function store(MountainTrainingRequest $request)
    {
        $data = MountainTraining::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'courses_count' => $request->count,
            'duration' => $request->duration,
            'age_group_start' => $request->start,
            'age_group_end' => $request->end,
            'course_fee' => $request->fee ?? null,
            'remarks' => $request->remarks ? trim($request->remarks) : null,
        ]);

        if ($request->file('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
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

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------

    public function update(MountainTrainingRequest $request, string $id)
    {
        $data = MountainTraining::findOrFail($id);

        MountainTraining::whereId($id)->update([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'courses_count' => $request->count,
            'duration' => $request->duration,
            'age_group_start' => $request->start,
            'age_group_end' => $request->end,
            'course_fee' => $request->fee ?? null,
            'remarks' => $request->remarks ? trim($request->remarks) : null,
        ]);

        if ($request->file('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
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

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------

    public function destroy(string $id)
    {
        $data = MountainTraining::findOrFail($id);
        $filePath = $data->file_path ? str_replace('/storage', '', $data->file_path) : null;

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        MountainTraining::where('id', $id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_NO_CONTENT);
    }

    // -------------------------------

    public function toggle(Request $request, String $id)
    {
        MountainTraining::where('id', $id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

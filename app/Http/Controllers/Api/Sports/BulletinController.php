<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpBulletin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BulletinController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpBulletin::when($search, function ($query, $search) {
            $query->where('name', 'ILIKE', "%{$search}%");
        })
            ->orderBy('id', 'desc')
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

    // -------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|max:255',
            'eventDate' => 'nullable|before_or_equal:today',
            'newFile' => 'nullable|max:5120',
        ], [], ['eventDate' => 'Event date', 'newFile' => 'Attachment']);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = SpBulletin::create([
            'name' => $request->name ? trim($request->name) : null,
            'slug' => $request->name ? Str::slug($request->name) : null,
            'event_date' => $data->eventDate ?? null,
        ]);

        if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/bulletins';
            $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            SpBulletin::whereId($data->id)->update([
                'file_path' => Storage::url($filePath),
                'file_name' => $fileOriginalName
            ]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|max:255',
            'eventDate' => 'nullable|before_or_equal:today',
            'newFile' => 'nullable|max:5120',
        ], [], ['eventDate' => 'Event date', 'newFile' => 'Attachment']);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = SpBulletin::findOrFail($id);

        SpBulletin::whereId($id)->update([
            'name' => $request->name ? trim($request->name) : null,
            'slug' => $request->name ? Str::slug($request->name) : null,
            'event_date' => $request->eventDate ?? null,
        ]);

        if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/bulletins';
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

            SpBulletin::whereId($id)->update([
                'file_path' => Storage::url($filePath),
                'file_name' => $fileOriginalName
            ]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------------------

    public function destroy(string $id)
    {
        $data = SpBulletin::findOrFail($id);

        if ($data->file_path) {
            $deletePath = str_replace('/storage', '', $data->file_path);

            if (Storage::disk('public')->exists($deletePath)) {
                Storage::disk('public')->delete($deletePath);
            }
        }

        SpBulletin::whereId($id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function toggle(Request $request, String $id)
    {
        SpBulletin::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

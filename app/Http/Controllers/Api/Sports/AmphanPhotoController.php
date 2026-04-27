<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpAmphanPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AmphanPhotoController extends Controller
{
    public function index()
    {
        $data = SpAmphanPhoto::orderBy('id', 'desc')->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|array',
            'image.*' => 'required|mimes:png,jpg,jpeg,webp|max:200',
            'title' => 'nullable|max:255'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($request->hasFile('image') && $request->file('image')[0]->getSize() > 0) {
            $file = $request->file('image')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/photo-galleries/amphan';

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            SpAmphanPhoto::create([
                'image_path' => Storage::url($filePath),
                'title' => $request->title ? trim($request->title) : null,
            ]);
        }

        return response()->json(['data' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------------------

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|array',
            'image.*' => 'nullable|mimes:png,jpg,jpeg,webp|max:200',
            'title' => 'nullable|max:255'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $photo = SpAmphanPhoto::findOrFail($id);

        SpAmphanPhoto::whereId($id)->update([
            'title' => $request->title ? trim($request->title) : null,
        ]);

        if ($request->hasFile('image') && $request->file('image')[0]->getSize() > 0) {
            $file = $request->file('image')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/photo-galleries/amphan';

            if ($photo->image_path) {
                $deletePath = str_replace('/storage', '', $photo->image_path);
                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            SpAmphanPhoto::whereId($id)->update([
                'image_path' => Storage::url($filePath),
            ]);
        }

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function destroy(string $id)
    {
        $photo = SpAmphanPhoto::findOrFail($id);

        if ($photo->image_path) {
            $deletePath = str_replace('/storage', '', $photo->image_path);
            if (Storage::disk('public')->exists($deletePath)) {
                Storage::disk('public')->delete($deletePath);
            }
        }
        $photo->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

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
        $search = request()->query('search');

        $data = SpAmphanPhoto::when($search, function ($query, $search) {
            $query->when('title', 'ILIKE', "%{$search}%");
        })
            ->orderBy('id', 'desc')
            ->paginate(9);

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
        $validator = Validator::make(
            $request->all(),
            [
                'newImage' => 'required|mimes:png,jpg,jpeg,webp|max:5120',
                'title' => 'nullable|max:255'
            ],
            ['newImage.max' => 'Image size cannot be more than 5 MB'],
            ['newImage' => 'Image']
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->hasFile('newImage') && $request->file('newImage')->getSize() > 0) {
            $file = $request->file('newImage');
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

    public function update(Request $request, String $id)
    {
        $validator = Validator::make($request->all(), [
            'newImage' => 'nullable|mimes:png,jpg,jpeg,webp|max:5120',
            'title' => 'nullable|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $photo = SpAmphanPhoto::findOrFail($id);

        SpAmphanPhoto::whereId($id)->update([
            'title' => $request->title ? trim($request->title) : null,
        ]);

        if ($request->hasFile('newImage') && $request->file('newImage')->getSize() > 0) {
            $file = $request->file('newImage')[0];
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

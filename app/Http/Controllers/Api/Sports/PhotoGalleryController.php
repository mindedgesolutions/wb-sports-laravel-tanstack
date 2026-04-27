<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\PhotoGalleryRequest;
use App\Http\Resources\Sports\PhotoGalleryResource;
use App\Models\SpPhoto;
use App\Models\SpPhotoGallery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PhotoGalleryController extends Controller
{
    public function index($category)
    {
        $data = SpPhotoGallery::withCount('photos')
            ->where('category', $category)
            ->orderBy('event_date')
            ->orderBy('title')
            ->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ------------------------------------------

    public function store(PhotoGalleryRequest $request)
    {
        $data = SpPhotoGallery::create([
            'category' => 'photo',
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'description' => $request->description ? trim($request->description) : null,
            'event_date' => $request->eventDate ? date('Y-m-d', strtotime($request->eventDate)) : null,
        ]);

        if ($request->hasFile('coverImg') && $request->file('coverImg')[0]->getSize() > 0) {
            $file = $request->file('coverImg')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/photo-galleries';

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            SpPhotoGallery::whereId($data->id)
                ->update(['cover_img' => Storage::url($filePath)]);
        }

        return response()->json(['data' => $data], Response::HTTP_CREATED);
    }

    // ------------------------------------------

    public function update(PhotoGalleryRequest $request, $id)
    {
        $photoGallery = SpPhotoGallery::findOrFail($id);

        SpPhotoGallery::whereId($id)->update([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'description' => $request->description ? trim($request->description) : null,
            'event_date' => $request->eventDate ? date('Y-m-d', strtotime($request->eventDate)) : null,
        ]);

        if ($request->hasFile('coverImg') && $request->file('coverImg')[0]->getSize() > 0) {
            $file = $request->file('coverImg')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/photo-galleries';

            if ($photoGallery) {
                $deletePath = str_replace('/storage', '', $photoGallery->cover_img);
                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            SpPhotoGallery::whereId($id)
                ->update(['cover_img' => Storage::url($filePath)]);
        }

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // ------------------------------------------

    public function destroy($id)
    {
        $photoGallery = SpPhotoGallery::findOrFail($id);

        if ($photoGallery->cover_img) {
            $deletePath = str_replace('/storage', '', $photoGallery->cover_img);
            if (Storage::disk('public')->exists($deletePath)) {
                Storage::disk('public')->delete($deletePath);
            }
        }
        $photoGallery->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // ------------------------------------------

    public function activate(Request $request, $id)
    {
        SpPhotoGallery::whereId($id)->update(['is_active' => $request->is_active]);

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // ------------------------------------------

    public function single($id)
    {
        $data = SpPhotoGallery::whereId($id)->first();

        return PhotoGalleryResource::make($data);
    }

    // ------------------------------------------

    public function storeImages(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:200',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            DB::beginTransaction();

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                    $directory = 'uploads/sports/photo-galleries/gallery/' . $id;

                    if (!Storage::disk('public')->exists($directory)) {
                        Storage::disk('public')->makeDirectory($directory);
                    }

                    $filePath = $file->storeAs($directory, $filename, 'public');

                    SpPhoto::create([
                        'gallery_id' => $id,
                        'image_path' => Storage::url($filePath),
                    ]);
                }
            }

            $updated = SpPhotoGallery::whereId($id)->first();

            DB::commit();

            return PhotoGalleryResource::make($updated);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['errors' => 'An error occurred while storing images.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------

    public function deleteImage($id)
    {
        try {
            DB::beginTransaction();

            $galleryId = SpPhoto::whereId($id)->pluck('gallery_id')->first();

            $image = SpPhoto::findOrFail($id);

            $deletePath = str_replace('/storage', '', $image->image_path);

            if (Storage::disk('public')->exists($deletePath)) {
                Storage::disk('public')->delete($deletePath);
            }

            SpPhoto::whereId($id)->delete();

            $updated = SpPhotoGallery::whereId($galleryId)->first();

            DB::commit();

            return PhotoGalleryResource::make($updated);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['errors' => 'An error occurred while deleting the image.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

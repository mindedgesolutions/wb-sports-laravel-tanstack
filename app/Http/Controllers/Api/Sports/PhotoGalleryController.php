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
use Illuminate\Support\Str;

class PhotoGalleryController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpPhotoGallery::withCount('photos')
            ->when($search, function ($query, $search) {
                $query->where('title', 'ILIKE', "%{$search}%");
            })
            ->orderBy('event_date')
            ->orderBy('title')
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

    // ------------------------------------------

    public function store(PhotoGalleryRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = SpPhotoGallery::create([
                'category' => 'photo',
                'title' => trim($request->title),
                'slug' => Str::slug($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'event_date' => $request->eventDate ?? null,
            ]);

            if ($request->file('coverImg') && $request->file('coverImg')->getSize() > 0) {
                $file = $request->file('coverImg');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/photo-galleries/' . $data->id;

                if ($data->cover_img) {
                    $deletePath = str_replace('/storage', '', $data->cover_img);
                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                $data->cover_img = Storage::url($filePath);
                $data->save();
            }

            DB::commit();

            return response()->json(['data' => $data], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json(['message' => 'Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------

    public function upload(Request $request, string $id)
    {
        try {
            $photos = [];

            DB::beginTransaction();

            if ($request->hasFile('galleryImg')) {
                foreach ($request->file('galleryImg') as $file) {
                    $filename = Str::ulid() . '.' . $file->extension();
                    $directory = "uploads/sports/photo-galleries/{$id}";

                    $filePath = $file->storeAs(
                        $directory,
                        $filename,
                        'public'
                    );

                    $photos[] = [
                        'gallery_id' => $id,
                        'image_path' => Storage::url($filePath),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($photos)) {
                SpPhoto::insert($photos);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json(['message' => 'Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------

    public function update(PhotoGalleryRequest $request, String $id)
    {
        try {
            DB::beginTransaction();

            $keepImages = explode(',', $request->existingGalleryImg);
            $data = SpPhotoGallery::with('photos')->findOrFail($id);

            if ($data->photos->isNotEmpty()) {
                $data->photos->each(
                    function ($img) use ($keepImages) {
                        if (!in_array($img->image_path, $keepImages)) {
                            $deletePath = str_replace('/storage', '', $img->image_path);
                            if (Storage::disk('public')->exists($deletePath)) {
                                Storage::disk('public')->delete($deletePath);
                            }
                            $img->delete();
                        }
                    }
                );
            }

            SpPhotoGallery::whereId($id)->update([
                'title' => trim($request->title),
                'slug' => Str::slug($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'event_date' => $request->eventDate ?? null,
            ]);

            if ($request->file('coverImg') && $request->file('coverImg')->getSize() > 0) {
                $file = $request->file('coverImg');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/photo-galleries/' . $id;

                if ($data->cover_img) {
                    $deletePath = str_replace('/storage', '', $data->cover_img);
                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                SpPhotoGallery::whereId($id)->update([
                    'cover_img' => Storage::url($filePath)
                ]);
            }

            $updated = SpPhotoGallery::with('photos')->findOrFail($id);

            DB::commit();

            return response()->json(['data' => $updated], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json(['message' => 'Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------

    public function destroy(String $id)
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

    public function toggle(Request $request, String $id)
    {
        SpPhotoGallery::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // ------------------------------------------

    public function show(String $id)
    {
        $data = SpPhotoGallery::with('photos')->findOrFail($id);

        return PhotoGalleryResource::make($data);
    }
}

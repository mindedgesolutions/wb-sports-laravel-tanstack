<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FairProgrammeGalleryResource;
use App\Models\FairProgrammeGallery;
use App\Models\FairProgrammGalleryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PhotoGalleryController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = FairProgrammeGallery::withCount('images')
            ->with([
                'images' => function ($query) {
                    $query->latest()->limit(3);
                }
            ])
            ->when($search, function ($query, $search) {
                $query->where('title', 'ilike', "%{$search}%");
            })
            ->orderBy('programme_date', 'desc')
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

    // --------------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'coverImg' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
        ], [], ['coverImg' => 'Cover image']);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = FairProgrammeGallery::create([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'programme_date' => $request->programDate ?? null,
            'description' => $request->description ? trim($request->description) : null,
            'added_by' => Auth::id(),
            'is_active' => true
        ]);

        if ($request->file('coverImg') && $request->file('coverImg')->getSize() > 0) {
            $file = $request->file('coverImg');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/photo-galleries/' . $data->id;

            if ($data->cover_image) {
                $deletePath = str_replace('/storage', '', $data->cover_image);
                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            $data->cover_image = Storage::url($filePath);
            $data->save();
        }
        return response()->json(['data' => $data], Response::HTTP_CREATED);
    }

    // --------------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'coverImg' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
        ], [], ['coverImg' => 'Cover image']);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $keepImages = explode(',', $request->existingGalleryImg);
        $data = FairProgrammeGallery::with('images')->findOrFail($id);

        if ($data->images->isNotEmpty()) {
            $data->images->each(
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

        FairProgrammeGallery::whereId($id)->update([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'description' => $request->description ? trim($request->description) : null,
            'updated_by' => Auth::id(),
            'programme_date' => $request->programDate ?? null,
        ]);

        if ($request->file('coverImg') && $request->file('coverImg')->getSize() > 0) {
            $file = $request->file('coverImg');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/photo-galleries/' . $id;

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

            $data->cover_image = Storage::url($filePath);
            $data->save();
        }

        $updated = FairProgrammeGallery::with('images')->findOrFail($id);

        return response()->json(['data' => $updated], Response::HTTP_OK);
    }

    // --------------------------------------------------

    public function upload(Request $request, string $id)
    {
        $photos = [];

        if ($request->hasFile('galleryImg')) {
            foreach ($request->file('galleryImg') as $file) {
                $filename = Str::ulid() . '.' . $file->extension();
                $directory = "uploads/services/photo-galleries/{$id}";

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
            FairProgrammGalleryImage::insert($photos);
        }
        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // --------------------------------------------------

    public function show(string $id)
    {
        $data = FairProgrammeGallery::findOrFail($id);

        return FairProgrammeGalleryResource::make($data);
    }

    // --------------------------------------------------

    public function destroy(string $id)
    {
        $directory = 'uploads/services/photo-galleries/' . $id;
        Storage::disk('public')->deleteDirectory($directory);

        FairProgrammGalleryImage::where('gallery_id')->delete();
        FairProgrammeGallery::whereId($id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------------

    public function toggle(Request $request, string $id)
    {
        FairProgrammeGallery::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\FifaRequest;
use App\Models\SpFifa;
use App\Models\SpFifaPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FifaController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpFifa::when($search, function ($query, $search) {
            $query->where('name', 'ILIKE', "%{$search}%")
                ->orWhere('description', 'ILIKE', "%{$search}%");
        })
            ->with('firstPhoto')
            ->withCount('photos')
            ->orderBy('event_date', 'desc')
            ->orderBy('name', 'asc')
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

    public function store(FifaRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = SpFifa::create([
                'name' => trim($request->title),
                'slug' => Str::slug($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'event_date' => $request->eventDate ?? null,
            ]);

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

            if ($request->hasFile('newGalleryImg')) {
                foreach ($request->file('newGalleryImg') as $file) {
                    $filename = Str::ulid() . '.' . $file->extension();
                    $directory = "uploads/sports/fifa/{$id}";

                    $filePath = $file->storeAs(
                        $directory,
                        $filename,
                        'public'
                    );

                    $photos[] = [
                        'fifa_id' => $id,
                        'image_path' => Storage::url($filePath),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($photos)) {
                SpFifaPhoto::insert($photos);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json(['message' => 'Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------

    public function show(String $id)
    {
        $data = SpFifa::with('photos')
            ->findOrFail($id);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ------------------------------------------

    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $keepImages = explode(',', $request->existingGalleryImg);
            $dbPhotos = SpFifa::with('photos')->findOrFail($id);

            if ($dbPhotos->photos->isNotEmpty()) {
                $dbPhotos->photos->each(
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

            $data = SpFifa::whereId($id)->update([
                'name' => trim($request->title),
                'slug' => Str::slug($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'event_date' => $request->eventDate ?? null,
            ]);

            DB::commit();

            return response()->json(['data' => $data], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json(['message' => 'Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $directory = 'uploads/sports/fifa/' . $id;
            Storage::disk('public')->deleteDirectory($directory);

            SpFifaPhoto::where('fifa_id', $id)->delete();

            SpFifa::whereId($id)->delete();

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while deleting the FIFA event.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------

    public function toggle(Request $request, string $id)
    {
        SpFifa::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }
}

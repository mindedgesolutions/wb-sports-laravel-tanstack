<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\StadiumRequest;
use App\Http\Resources\Sports\StadiumResource;
use App\Models\SpStadium;
use App\Models\SpStadiumDetail;
use App\Models\SpStadiumHighlight;
use App\Models\SpStadiumImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StadiumController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpStadium::when($search, function ($query, $search) {
            $query->where('name', 'ILIKE', "%{$search}%")
                ->orWhere('location', 'ILIKE', "%{$search}%");
        })
            ->orderBy('name', 'asc')
            ->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total(),
            ]
        ], Response::HTTP_OK);
    }

    // ------------------------------------------------

    public function store(StadiumRequest $request)
    {
        try {
            DB::beginTransaction();

            $master = SpStadium::create([
                'name' => trim($request->name),
                'slug' => Str::slug($request->name),
                'address' => $request->address ? trim($request->address) : null,
                'location' => trim($request->location),
            ]);

            if ($request->file('coverImg') && $request->file('coverImg')->getSize() > 0) {
                $file = $request->file('coverImg');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/stadiums/' . $master->id;

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                $master->cover_img = Storage::url($filePath);
                $master->save();
            }

            SpStadiumDetail::updateOrCreate([
                'stadium_id' => $master->id,
            ], [
                'description' => $request->description,
            ]);

            $imagePaths = [];

            if ($request->hasFile('newGalleryImg')) {
                foreach ($request->file('newGalleryImg') as $file) {
                    $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                    $directory = 'uploads/sports/stadiums/' . $master->id . '/gallery';

                    if (!Storage::disk('public')->exists($directory)) {
                        Storage::disk('public')->makeDirectory($directory);
                    }

                    $filePath = $file->storeAs($directory, $filename, 'public');

                    $imagePaths[] = [
                        'stadium_id' => $master->id,
                        'image_path' => Storage::url($filePath),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($imagePaths)) {
                SpStadiumImage::insert($imagePaths);
            }

            $highlightsData = [];

            if ($request->highlights) {
                foreach ($request->highlights as $item) {
                    if (!empty($item['value'])) {
                        $highlightsData[] = [
                            'stadium_id' => $master->id,
                            'title' => trim($item['value']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            if (!empty($highlightsData)) {
                SpStadiumHighlight::insert($highlightsData);
            }

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Error occurred while creating stadium'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------------

    public function show(String $id)
    {
        $data = SpStadium::with([
            'stadiumDetails',
            'stadiumHighlights',
            'images'
        ])->findOrFail($id);

        return StadiumResource::make($data);
    }

    // ------------------------------------------------

    public function update(StadiumRequest $request, $id)
    {
        $data = SpStadium::with('images')->findOrFail($id);

        try {
            DB::beginTransaction();

            $master = SpStadium::whereId($data->id)->update([
                'name' => trim($request->name),
                'slug' => Str::slug($request->name),
                'address' => $request->address ? trim($request->address) : null,
                'location' => trim($request->location),
            ]);

            if ($request->file('coverImg') && $request->file('coverImg')->getSize() > 0) {
                $file = $request->file('coverImg');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/stadiums/' . $data->id;

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

            SpStadiumDetail::updateOrCreate([
                'stadium_id' => $data->id,
            ], [
                'description' => $request->description,
            ]);

            // Handle images (delete and add new ones) start ------------

            $directory = 'uploads/sports/stadiums/' . $data->id . '/gallery';

            $keepImages = explode(',', $request->existingGalleryImg);

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

            $imagePaths = [];

            if ($request->hasFile('newGalleryImg')) {
                foreach ($request->file('newGalleryImg') as $file) {
                    $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();

                    if (!Storage::disk('public')->exists($directory)) {
                        Storage::disk('public')->makeDirectory($directory);
                    }

                    $filePath = $file->storeAs($directory, $filename, 'public');

                    $imagePaths[] = [
                        'stadium_id' => $data->id,
                        'image_path' => Storage::url($filePath),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($imagePaths)) {
                SpStadiumImage::insert($imagePaths);
            }

            // Handle images (delete and add new ones) end ------------

            // Handle highlights start ------------

            $highlightsData = [];

            SpStadiumHighlight::where('stadium_id', $data->id)->delete();

            if ($request->highlights) {
                foreach ($request->highlights as $item) {
                    if (!empty($item['value'])) {
                        $highlightsData[] = [
                            'stadium_id' => $data->id,
                            'title' => trim($item['value']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            if (!empty($highlightsData)) {
                SpStadiumHighlight::insert($highlightsData);
            }

            // Handle highlights end ------------

            $updated = SpStadium::with([
                'stadiumDetails',
                'stadiumHighlights',
                'images'
            ])->findOrFail($id);

            DB::commit();

            return StadiumResource::make($updated);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Error occurred while creating stadium'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------------

    public function destroy($id)
    {
        $stadium = SpStadium::findOrFail($id);

        try {
            DB::beginTransaction();

            if ($stadium->cover_img) {
                $deletePath = str_replace('/storage', '', $stadium->cover_img);
                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }
            $images = SpStadiumImage::where('stadium_id', $id)->get();

            if ($images->isNotEmpty()) {
                foreach ($images as $image) {
                    $deletePath = str_replace('/storage', '', $image->image_path);
                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }
            }
            $images->each->delete();

            SpStadiumHighlight::where('stadium_id', $id)->delete();
            SpStadiumDetail::where('stadium_id', $id)->delete();

            $directory = 'uploads/sports/stadiums/' . $id;
            Storage::disk('public')->deleteDirectory($directory);

            $stadium->delete();

            DB::commit();
            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Error occurred while deleting stadium'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //  ------------------------------------------------

    public function toggle(Request $request, $id)
    {
        SpStadium::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

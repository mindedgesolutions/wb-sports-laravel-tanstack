<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpFifa;
use App\Models\SpFifaPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FifaController extends Controller
{
    public function index()
    {
        $data = SpFifa::with('firstPhoto')
            ->withCount('photos')
            ->orderBy('event_date', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ------------------------------------------

    public function store(Request $request)
    {
        $data = $request->all();
        $data['eventDate'] = Date::createFromFormat('d/m/Y', $data['eventDate']);

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'max:255', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SpFifa::where('slug', $inputSlug)->exists()) {
                    $fail('Gallery exists.');
                }
            }],
            'description' => 'nullable',
            'eventDate' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        SpFifa::create([
            'name' => trim($request->title),
            'slug' => Str::slug($request->title),
            'description' => $request->description ? trim($request->description) : null,
            'event_date' => $request->eventDate ? date('Y-m-d', strtotime($request->eventDate)) : null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // ------------------------------------------

    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $data['eventDate'] = Date::createFromFormat('d/m/Y', $data['eventDate']);

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'max:255', function ($attribute, $value, $fail) use ($id) {
                $inputSlug = Str::slug($value);
                if (SpFifa::where('slug', $inputSlug)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    $fail('Gallery exists.');
                }
            }],
            'description' => 'nullable',
            'eventDate' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        SpFifa::whereId($id)->update([
            'name' => trim($data['title']),
            'slug' => Str::slug($data['title']),
            'description' => $data['description'] ? trim($data['description']) : null,
            'event_date' => $data['eventDate'] ? date('Y-m-d', strtotime($data['eventDate'])) : null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // ------------------------------------------

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $photos = SpFifaPhoto::where('fifa_id', $id)->get();

            $deletePaths = $photos->map(function ($photo) {
                return str_replace('/storage', '', $photo->image_path);
            })->toArray();

            Storage::disk('public')->delete($deletePaths);

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

    public function activate(Request $request, string $id)
    {
        SpFifa::whereId($id)->update(['is_active' => $request->is_active]);

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // ------------------------------------------

    public function single(string $id)
    {
        $fifa = SpFifa::with('photos')->whereId($id)->first();

        return response()->json(['data' => $fifa], Response::HTTP_OK);
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
                    $directory = 'uploads/sports/fifa/' . $id;

                    if (!Storage::disk('public')->exists($directory)) {
                        Storage::disk('public')->makeDirectory($directory);
                    }

                    $filePath = $file->storeAs($directory, $filename, 'public');

                    SpFifaPhoto::create([
                        'fifa_id' => $id,
                        'image_path' => Storage::url($filePath),
                    ]);
                }
            }

            $updated = SpFifa::with('photos')->whereId($id)->first();

            DB::commit();

            return response()->json(['data' => $updated], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['errors' => 'An error occurred while storing images.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------------

    public function deleteImage($id)
    {
        $photo = SpFifaPhoto::findOrFail($id);

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

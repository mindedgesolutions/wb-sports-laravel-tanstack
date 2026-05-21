<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpHomepageSlider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HomepageSliderController extends Controller
{
    public function index()
    {
        $data = SpHomepageSlider::orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total()
            ]
        ], Response::HTTP_OK);
    }

    // ---------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'newImage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ], [
            'newImage.required' => 'Image is required',
            'newImage.image' => 'File must be an image',
            'newImage.mimes' => 'Image must be a file of type: jpeg, png, jpg, gif, svg',
            'newImage.max' => 'Image may not be greater than 10 MB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $filePath = '';

            if ($request->hasFile('newImage') && $request->file('newImage')->getSize() > 0) {
                $file = $request->file('newImage');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/homepage-sliders';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');
            }

            SpHomepageSlider::create([
                'image_path' => Storage::url($filePath),
                'is_active' => true
            ]);

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ---------------------------

    public function destroy(string $id)
    {
        $slider = SpHomepageSlider::findOrFail($id);
        $imagePath = str_replace('/storage/', '', $slider->image_path);

        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        $slider->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // ---------------------------

    public function toggle(Request $request, $id)
    {
        SpHomepageSlider::where('id', $id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

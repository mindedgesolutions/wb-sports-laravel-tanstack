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

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ---------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slider' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:300',
        ], [
            'slider.required' => 'Slider image is required',
            'slider.image' => 'File must be an image',
            'slider.mimes' => 'Image must be a file of type: jpeg, png, jpg, gif, svg',
            'slider.max' => 'Image may not be greater than 300 KB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            if ($request->hasFile('slider')) {
                $file = $request->file('slider');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/homepage-sliders';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');
            }

            SpHomepageSlider::create([
                'image_path' => Storage::url($filePath),
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

    public function activate(Request $request, $id)
    {
        SpHomepageSlider::where('id', $id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

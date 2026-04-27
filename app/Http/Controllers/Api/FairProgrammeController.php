<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FairProgramResource;
use App\Models\FairProgramme;
use App\Models\FairProgrammeGallery;
use App\Models\FairProgrammGalleryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class FairProgrammeController extends Controller
{
    public function fpList()
    {
        $data = FairProgramme::where('organisation', 'services')->orderBy('id', 'desc')->paginate(10);

        return FairProgramResource::collection($data);
    }

    // ------------------------------------

    public function fpStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'occurance' => ['required', Rule::in(['one-time', 'recurring'])],
            'cover' => 'nullable|array',
            'cover.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slug = Str::slug($request->title);
        $check = FairProgramme::where('slug', $slug)->first();
        if ($check) {
            return response()->json(['errors' => ['title' => ['Programme with this title already exists.']]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->hasFile('cover') && $request->file('cover')[0]->getSize() > 0) {
            $file = $request->file('cover')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/fairs-programmes';

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $filePath = $file->storeAs($directory, $filename, 'public');
        } else {
            $filePath = null;
        }

        $data = FairProgramme::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'occurance' => $request->occurance,
            'description' => $request->description ?? null,
            'uuid' => Str::uuid(),
            'added_by' => Auth::id(),
            'organisation' => 'services',
            'cover_image' => $filePath ? Storage::url($filePath) : null,
        ]);

        return response()->json(['uuid' => $data->uuid, 'data' => $data], Response::HTTP_CREATED);
    }

    // ------------------------------------

    public function fpEdit($uuid)
    {
        $data = FairProgramme::where('uuid', $uuid)->first();

        if (!$data) {
            return response()->json(['errors' => ['uuid' => ['Programme not found.']]], Response::HTTP_NOT_FOUND);
        }

        return FairProgramResource::make($data);
    }

    // ------------------------------------

    public function fpUpdate(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'occurance' => ['required', Rule::in(['one-time', 'recurring'])],
            'cover' => 'nullable|array',
            'cover.*' => 'file|image|mimes:jpeg,png,jpg,gif,svg|max:200'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = FairProgramme::where('uuid', $uuid)->first();

        if (!$data) {
            return response()->json(['errors' => ['title' => ['Programme not found.']]], Response::HTTP_NOT_FOUND);
        }

        $slug = Str::slug($request->title);
        $check = FairProgramme::where('slug', $slug)->where('uuid', '!=', $uuid)->first();
        if ($check) {
            return response()->json(['errors' => ['title' => ['Programme with this title already exists.']]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->hasFile('cover') && $request->file('cover')[0]->getSize() > 0) {
            if ($data) {
                $deletePath = str_replace('/storage', '', $data->cover_image);

                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            $file = $request->file('cover')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/fairs-programmes';

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $filePath = $file->storeAs($directory, $filename, 'public');
        } else {
            $filePath = null;
        }

        $data->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'occurance' => $request->occurance,
            'description' => $request->description ?? null,
            'cover_image' => $filePath ? Storage::url($filePath) : $data->cover_image ?? null,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['uuid' => $data->uuid, 'data' => $data], Response::HTTP_OK);
    }

    // ------------------------------------

    public function fpDestroy($id)
    {
        try {
            DB::beginTransaction();

            $galleries = FairProgrammeGallery::where('program_id', $id);

            $galleryRecords = $galleries->get();
            foreach ($galleryRecords as $gallery) {
                $images = FairProgrammGalleryImage::where('gallery_id', $gallery->id)->get();

                foreach ($images as $image) {
                    $deletePath = str_replace('/storage', '', $image->image_path);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                FairProgrammGalleryImage::where('gallery_id', $gallery->id)->delete();
                $gallery->delete();
            }

            $galleries->delete();
            $data = FairProgramme::find($id);
            if ($data) {
                $deletePath = str_replace('/storage', '', $data->cover_image);

                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }
            FairProgramme::where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'Success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('Error in fpDestroy: ' . $th->getMessage());
            DB::rollBack();
            return response()->json(['errors' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------

    public function fpGalleryStore(Request $request)
    {
        $request->merge([
            'programmeDate' => Date::createFromFormat('d/m/Y', $request->programmeDate)
        ]);

        $validator = Validator::make($request->all(), [
            'galleryTitle' => 'required',
            'programmeDate' => 'required|before:today',
            'description' => 'nullable',
            'images' => 'required|array',
            'images.*' => 'file|image|mimes:jpeg,png,jpg,gif,svg|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $data = FairProgramme::where('uuid', $request->uuid)->first();
            if (!$data) {
                return response()->json(['errors' => ['Programme not found.']], Response::HTTP_NOT_FOUND);
            }
            $fpid = $data->id;

            $insert = FairProgrammeGallery::create([
                'program_id' => $fpid,
                'title' => $request->galleryTitle,
                'slug' => Str::slug($request->galleryTitle),
                'programme_date' => date('Y-m-d', strtotime($request->programmeDate)),
                'description' => $request->description ? trim($request->description) : null,
                'organisation' => 'services',
                'added_by' => Auth::id(),
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                    $directory = 'uploads/services/fairs-programmes/gallery/' . $fpid;

                    if (!Storage::disk('public')->exists($directory)) {
                        Storage::disk('public')->makeDirectory($directory);
                    }

                    $filePath = $file->storeAs($directory, $filename, 'public');

                    FairProgrammGalleryImage::create([
                        'gallery_id' => $insert->id,
                        'image_path' => Storage::url($filePath),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error('Error in fpGalleryStore: ' . $th->getMessage());
            DB::rollBack();
            return response()->json(['errors' => ['Something went wrong. Please try again later.']], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------

    public function fpGalleryUpdate(Request $request, $id)
    {
        $request->merge([
            'programmeDate' => Date::createFromFormat('d/m/Y', $request->programmeDate)
        ]);

        $validator = Validator::make($request->all(), [
            'galleryTitle' => 'required',
            'programmeDate' => 'required|before:today',
            'description' => 'nullable',
            'images' => 'nullable|file|array',
            'images.*' => 'file|image|mimes:jpeg,png,jpg,gif,svg|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $data = FairProgrammeGallery::find($id);
            if (!$data) {
                return response()->json(['errors' => ['Gallery not found.']], Response::HTTP_NOT_FOUND);
            }

            $data->update([
                'title' => trim($request->galleryTitle),
                'slug' => Str::slug($request->galleryTitle),
                'programme_date' => date('Y-m-d', strtotime($request->programmeDate)),
                'description' => $request->description ? trim($request->description) : null,
                'updated_by' => Auth::id(),
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                    $directory = 'uploads/services/fairs-programmes/gallery/' . $data->program_id;

                    if (!Storage::disk('public')->exists($directory)) {
                        Storage::disk('public')->makeDirectory($directory);
                    }

                    $filePath = $file->storeAs($directory, $filename, 'public');

                    FairProgrammGalleryImage::create([
                        'gallery_id' => $data->id,
                        'image_path' => Storage::url($filePath),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('Error in fpGalleryUpdate: ' . $th->getMessage());
            DB::rollBack();
            return response()->json(['errors' => ['Something went wrong. Please try again later.']], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------

    public function fpGalleryDestroy($id)
    {
        try {
            DB::beginTransaction();

            $images = FairProgrammGalleryImage::where('gallery_id', $id)->get();

            foreach ($images as $image) {
                $deletePath = str_replace('/storage', '', $image->image_path);

                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            FairProgrammGalleryImage::where('gallery_id', $id)->delete();
            FairProgrammeGallery::where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'Success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('Error in fpGalleryDestroy: ' . $th->getMessage());
            DB::rollBack();
            return response()->json(['errors' => ['Error']], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------

    public function fpGalleryImageDestroy($id)
    {
        try {
            DB::beginTransaction();

            $image = FairProgrammGalleryImage::find($id);
            if (!$image) {
                return response()->json(['errors' => ['Image not found.']], Response::HTTP_NOT_FOUND);
            }

            $deletePath = str_replace('/storage', '', $image->image_path);

            if (Storage::disk('public')->exists($deletePath)) {
                Storage::disk('public')->delete($deletePath);
            }

            $image->delete();

            DB::commit();

            return response()->json(['message' => 'Success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('Error in fpGalleryImageDestroy: ' . $th->getMessage());
            DB::rollBack();
            return response()->json(['errors' => ['Error']], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ------------------------------------

    public function fpShowInGallery($id)
    {
        $image = FairProgrammeGallery::find($id);
        if (!$image) {
            return response()->json(['errors' => ['Image not found.']], Response::HTTP_NOT_FOUND);
        }
        $image->update(['show_in_gallery' => !$image->show_in_gallery]);

        return response()->json(['message' => 'Success'], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannerRequest;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $data = Banner::where('organization', 'services')
            ->with('banner_added_by', 'banner_updated_by')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function store(BannerRequest $request)
    {
        try {
            DB::beginTransaction();

            $check = Banner::where('page_url', $request->page)->first();
            $filePath = '';

            if ($request->hasFile('banner')) {
                $file = $request->file('banner')[0];
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/services/banners';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                if ($check) {
                    $deletePath = str_replace('/storage', '', $check->image_path);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                $filePath = $file->storeAs($directory, $filename, 'public');
            }

            if ($check) {
                Banner::where('page_url', $request->page)->update([
                    'page_title' => trim($request->pageTitle) ?? null,
                    'updated_by' => Auth::id(),
                    'image_path' => Storage::url($filePath),
                ]);
            } else {
                Banner::create([
                    'page_url' => $request->page,
                    'page_title' => trim($request->pageTitle) ?? null,
                    'added_by' => Auth::id(),
                    'image_path' => Storage::url($filePath),
                    'organization' => 'services',
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // --------------------------------------------

    public function bannerUpdate(BannerRequest $request, $id)
    {
        $data = Banner::findOrFail($id);

        try {
            DB::beginTransaction();

            $filePath = '';

            if ($request->hasFile('banner') && $request->file('banner')[0]->getSize() > 0) {
                $file = $request->file('banner')[0];
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/services/banners';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                if ($data) {
                    $deletePath = str_replace('/storage', '', $data->image_path);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                $filePath = $file->storeAs($directory, $filename, 'public');
            }

            Banner::where('id', $id)->update([
                'page_title' => trim($request->pageTitle) ?? $data->page_title,
                'updated_by' => Auth::id(),
                'image_path' => $request->hasFile('banner') ? Storage::url($filePath) : $data->image_path,
            ]);

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // --------------------------------------------

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $data = Banner::findOrFail($id);
            $filePath = str_replace('/storage', '', $data->image_path);

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            Banner::where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // --------------------------------------------

    public function activate(Request $request, string $id)
    {
        Banner::where('id', $id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function pageBanner()
    {
        $data = Banner::where('page_url', request()->query('url'))
            ->where('organization', 'services')
            ->where('is_active', true)
            ->select('image_path', 'page_title')
            ->first();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }
}

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
        $search = request()->query('search');

        $data = Banner::where('organization', 'services')
            ->when($search, function ($query, $search) {
                $query->where('page_title', 'ILIKE', "%{$search}%");
            })
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

    // --------------------------------------------

    public function store(BannerRequest $request)
    {
        try {
            DB::beginTransaction();

            $check = Banner::where('page_url', $request->page)->first();
            $filePath = '';

            if ($request->hasFile('newImg')) {
                $file = $request->file('newImg');
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
                    'page_title' => trim($request->title) ?? null,
                    'updated_by' => Auth::id(),
                    'image_path' => Storage::url($filePath),
                ]);
            } else {
                Banner::create([
                    'page_url' => $request->page,
                    'page_title' => trim($request->title) ?? null,
                    'added_by' => Auth::id(),
                    'image_path' => Storage::url($filePath),
                    'organization' => 'services',
                    'is_active' => true
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

    public function update(BannerRequest $request, String $id)
    {
        $data = Banner::findOrFail($id);

        try {
            DB::beginTransaction();

            $filePath = '';

            if ($request->hasFile('newImg') && $request->file('newImg')->getSize() > 0) {
                $file = $request->file('newImg');
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
                'page_title' => trim($request->title) ?? $data->page_title,
                'updated_by' => Auth::id(),
                'image_path' => $request->hasFile('newImg') ? Storage::url($filePath) : $data->image_path,
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

    public function toggle(Request $request, string $id)
    {
        Banner::where('id', $id)->update(['is_active' => $request->checked]);

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

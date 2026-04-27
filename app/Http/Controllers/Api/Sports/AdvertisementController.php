<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\SpAdvertisementRequest;
use App\Models\SpAdvertisement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdvertisementController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpAdvertisement::when($search, function ($query, $search) {
            $query->where('title', 'ILIKE', "%{$search}%")
                ->orWhere('description', 'ILIKE', "%{$search}%");
        })
            ->orderBy('ad_date', 'desc')
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

    // ----------------------------------------

    public function store(SpAdvertisementRequest $request)
    {
        $data = SpAdvertisement::create([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'description' => $request->description ? trim($request->description) : null,
            'ad_date' => $request->adDate ?? null,
        ]);

        if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/advertisements';
            $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');
            SpAdvertisement::whereId($data->id)->update([
                'file_path' => Storage::url($filePath),
                'file_name' => $fileOriginalName,
            ]);
        }

        return response()->json(['data' => 'success'], Response::HTTP_CREATED);
    }

    // ----------------------------------------

    public function update(SpAdvertisementRequest $request, string $id)
    {
        $data = SpAdvertisement::findOrFail($id);

        SpAdvertisement::whereId($id)->update([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'description' => $request->description ? trim($request->description) : null,
            'ad_date' => $request->adDate ?? null,
        ]);

        if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/advertisements';
            $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if ($data->file_path) {
                $deletePath = str_replace('/storage', '', $data->file_path);

                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');
            SpAdvertisement::whereId($data->id)->update([
                'file_path' => Storage::url($filePath),
                'file_name' => $fileOriginalName,
            ]);
        }

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // ----------------------------------------

    public function destroy(string $id)
    {
        $data = SpAdvertisement::findOrFail($id);

        $filePath = str_replace('/storage', '', $data->file_path);

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        SpAdvertisement::where('id', $id)->delete();

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // ----------------------------------------

    public function toggle(Request $request, string $id)
    {
        SpAdvertisement::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }
}

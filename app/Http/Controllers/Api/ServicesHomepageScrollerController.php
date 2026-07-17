<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServicesHomepageScrollerRequest;
use App\Models\ServicesHomepageScroller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServicesHomepageScrollerController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = ServicesHomepageScroller::when($search, function ($query, $search) {
            $query->where('title', 'ilike', "%{$search}%")
                ->orWhere('link', 'ilike', "%{$search}%");
        })
            ->orderBy('event_date', 'desc')
            ->orderBy('created_at', 'desc')
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

    // -----------------------------------------

    public function store(ServicesHomepageScrollerRequest $request)
    {
        $link = ($request->type === 'link' && $request->link) ? trim($request->link) : null;

        $data = ServicesHomepageScroller::create([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'event_date' => $request->eventDate ?? null,
            'type' => $request->type,
            'link' => $link
        ]);

        if ($request->type === 'attachment' && $request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/homepage-scrollers';
            $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            ServicesHomepageScroller::whereId($data->id)->update([
                'file_name' => $fileOriginalName,
                'file_path' => Storage::url($filePath),
            ]);
        }
        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -----------------------------------------

    public function update(ServicesHomepageScrollerRequest $request, string $id)
    {
        $data = ServicesHomepageScroller::findOrFail($id);
        $link = ($request->type === 'link' && $request->link) ? trim($request->link) : null;

        $fileName = '';
        $filePath = '';
        if ($request->type === 'link') {
            if ($data->file_path) {
                $deletePath = str_replace('/storage', '', $data->file_path);

                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }
            $fileName = null;
            $filePath = null;
        }

        ServicesHomepageScroller::whereId($id)->update([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'event_date' => $request->eventDate ?? null,
            'type' => $request->type,
            'link' => $link,
            'file_name' => $fileName,
            'file_path' => $filePath
        ]);

        if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/homepage-scrollers';
            $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            if ($data->file_path) {
                $deletePath = str_replace('/storage', '', $data->file_path);

                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            $filePath = $file->storeAs($directory, $filename, 'public');

            ServicesHomepageScroller::whereId($data->id)->update([
                'file_name' => $fileOriginalName,
                'file_path' => Storage::url($filePath),
            ]);
        }
        $updated = ServicesHomepageScroller::whereId($id)->get();

        return response()->json(['data' => $updated], Response::HTTP_OK);
    }

    // -----------------------------------------

    public function destroy(string $id)
    {
        $data = ServicesHomepageScroller::findOrFail($id);
        $filePath = str_replace('/storage', '', $data->file_path);

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        ServicesHomepageScroller::where('id', $id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -----------------------------------------

    public function toggle(Request $request, string $id)
    {
        ServicesHomepageScroller::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

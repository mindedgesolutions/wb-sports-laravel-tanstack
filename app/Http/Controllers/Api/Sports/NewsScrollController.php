<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\NewsScrollRequest;
use App\Models\SpNewsScroll;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NewsScrollController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpNewsScroll::when($search, function ($query, $search) {
            $query->where('title', 'ILIKE', "%{$search}%")
                ->orWhere('description', 'ILIKE', "%{$search}%");
        })
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

    // -------------------------------------------

    public function store(NewsScrollRequest $request)
    {
        $data = SpNewsScroll::create([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'description' => $request->description ? trim($request->description) : null,
            'news_date' => $request->newsDate ?? null,
        ]);

        if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/news-scroll';
            $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');
            SpNewsScroll::whereId($data->id)->update([
                'file_path' => Storage::url($filePath),
                'file_name' => $fileOriginalName,
            ]);
        }

        return response()->json(['data' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------------------

    public function update(NewsScrollRequest $request, string $id)
    {
        $data = SpNewsScroll::findOrFail($id);

        SpNewsScroll::whereId($id)->update([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'description' => $request->description ? trim($request->description) : null,
            'news_date' => $request->newsDate ?? null,
        ]);

        if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/news-scroll';
            $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if ($data->file_path) {
                $filePath = str_replace('/storage/', '', $data->file_path);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            SpNewsScroll::whereId($data->id)->update([
                'file_path' => Storage::url($filePath),
                'file_name' => $fileOriginalName,
            ]);
        }

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function destroy(string $id)
    {
        $data = SpNewsScroll::findOrFail($id);

        $filePath = str_replace('/storage', '', $data->file_path);

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        SpNewsScroll::where('id', $id)->delete();

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function toggle(Request $request, String $id)
    {
        SpNewsScroll::where('id', $id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsEventsRequest;
use App\Models\NewsEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsEventsController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = NewsEvent::when($search, function ($query, $search) {
            $query->where('title', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%");
        })
            ->orderBy('event_date', 'desc')
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

    // -----------------------------------------------

    public function store(NewsEventsRequest $request)
    {
        try {
            DB::beginTransaction();

            $eventDate = explode('-', $request->eventDate);

            $data = NewsEvent::create([
                'title' => trim($request->title),
                'slug' => Str::slug($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'event_date' => $request->eventDate,
                'type' => $request->type,
                'event_year' => $eventDate[0],
            ]);

            if ($request->hasFile('newFile')) {
                $file = $request->file('newFile');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/services/news-events';
                $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                NewsEvent::whereId($data->id)->update([
                    'file_path' => Storage::url($filePath),
                    'file_name' => $fileOriginalName
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

    // -----------------------------------------------

    public function update(NewsEventsRequest $request, string $id)
    {
        $data = NewsEvent::whereId($id)->first();
        $eventDate = explode('-', $request->eventDate);

        try {
            DB::beginTransaction();

            NewsEvent::whereId($id)->update([
                'title' => trim($request->title),
                'slug' => Str::slug($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'event_date' => $request->eventDate,
                'type' => $request->type,
                'event_year' => $eventDate[0],
            ]);

            if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
                $file = $request->file('newFile');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/services/news-events';
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

                NewsEvent::whereId($id)->update([
                    'file_path' => Storage::url($filePath),
                    'file_name' => $fileOriginalName
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -----------------------------------------------

    public function destroy(string $id)
    {
        $data = NewsEvent::findOrFail($id);
        $filePath = str_replace('/storage', '', $data->file_path);

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        NewsEvent::where('id', $id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -----------------------------------------------

    public function toggle(Request $request, String $id)
    {
        NewsEvent::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

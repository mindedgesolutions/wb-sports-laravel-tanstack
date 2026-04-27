<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsEventsRequest;
use App\Models\NewsEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsEventsController extends Controller
{
    public function index()
    {
        $data = NewsEvent::orderBy('event_date', 'desc')->paginate(10);
        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -----------------------------------------------

    public function store(NewsEventsRequest $request)
    {
        try {
            DB::beginTransaction();

            $eventSlug = Str::slug($request->title);
            $check = NewsEvent::where('slug', $eventSlug)->first();
            $filePath = '';
            $eventYear = date('Y', strtotime($request->eventDate));

            if ($request->hasFile('file')) {
                $file = $request->file('file')[0];
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/services/news-events';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                if ($check) {
                    $deletePath = str_replace('/storage', '', $check->file_path);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                $filePath = $file->storeAs($directory, $filename, 'public');
            }

            NewsEvent::create([
                'title' => trim($request->title),
                'slug' => $eventSlug,
                'description' => $request->description ? trim($request->description) : null,
                'file_path' => Storage::url($filePath),
                'event_date' => date('Y-m-d', strtotime($request->eventDate)),
                'type' => $request->type,
                'event_year' => $eventYear,
            ]);


            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -----------------------------------------------

    public function updateNews(NewsEventsRequest $request, string $id)
    {
        $data = NewsEvent::whereId($id)->first();
        $eventYear = date('Y', strtotime($request->eventDate));

        try {
            DB::beginTransaction();

            $filePath = '';

            if ($request->hasFile('file') && $request->file('file')[0]->getSize() > 0) {
                $file = $request->file('file')[0];
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/services/news-events';

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
            }

            NewsEvent::where('id', $id)->update([
                'title' => trim($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'file_path' => $request->hasFile('file') ? Storage::url($filePath) : $data->file_path,
                'event_date' => date('Y-m-d', strtotime($request->eventDate)),
                'type' => $request->type,
                'event_year' => $eventYear,
            ]);

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
        try {
            DB::beginTransaction();

            $data = NewsEvent::findOrFail($id);
            $filePath = str_replace('/storage', '', $data->file_path);

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            NewsEvent::where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -----------------------------------------------

    public function activate(Request $request, $id)
    {
        NewsEvent::whereId($id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

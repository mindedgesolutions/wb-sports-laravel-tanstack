<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\SpAnnouncementRequest;
use App\Models\SpAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementsController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpAnnouncement::when($search, function ($query, $search) {
            $query->where('ann_no', 'ILIKE', "%{$search}%")
                ->orWhere('subject', 'ILIKE', "%{$search}%");
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

    // -----------------------------

    public function store(SpAnnouncementRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = SpAnnouncement::create([
                'type' => $request->type,
                'ann_no' => trim($request->annNo),
                'subject' => trim($request->subject),
                'is_new' => false,
                'start_date' => $request->startDate ? $request->startDate : null,
                'end_date' => $request->endDate ? $request->endDate : null,
                'created_by' => Auth::id(),
            ]);

            if ($request->hasFile('newFile')) {
                $file = $request->file('newFile');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/announcements';
                $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                SpAnnouncement::whereId($data->id)->update([
                    'file_path' => Storage::url($filePath),
                    'file_name' => $fileOriginalName,
                ]);
            }


            DB::commit();

            return response()->json(['message' => 'added'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -----------------------------

    public function update(SpAnnouncementRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $announcement = SpAnnouncement::whereId($id)->first();

            SpAnnouncement::whereId($id)->update([
                'type' => $request->type,
                'ann_no' => trim($request->annNo),
                'subject' => trim($request->subject),
                'start_date' => $request->startDate ? $request->startDate : null,
                'end_date' => $request->endDate ? $request->endDate : null,
                'updated_by' => Auth::id(),
            ]);

            if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
                $file = $request->file('newFile');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/announcements';
                $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                if ($announcement) {
                    $deletePath = str_replace('/storage', '', $announcement->file_path);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }

                $filePath = $file->storeAs($directory, $filename, 'public');

                SpAnnouncement::whereId($announcement->id)->update([
                    'file_path' => Storage::url($filePath),
                    'file_name' => $fileOriginalName,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'updated'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -----------------------------

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $data = SpAnnouncement::findOrFail($id);
            $filePath = $data->file_path ? str_replace('/storage', '', $data->file_path) : null;

            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            SpAnnouncement::where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'deleted'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while processing your request.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -----------------------------

    public function toggle(Request $request, $id)
    {
        SpAnnouncement::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'updated'], Response::HTTP_OK);
    }
}

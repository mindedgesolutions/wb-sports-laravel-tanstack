<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\RtiNoticeRequest;
use App\Models\SpRtiNotice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RtiNoticeController extends Controller
{
    public function index()
    {
        $data = SpRtiNotice::orderBy('start_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -----------------------------------------------

    public function store(RtiNoticeRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = SpRtiNotice::create([
                'notice_no' => trim($request->noticeNo),
                'subject' => trim($request->subject),
                'is_new' => $request->isNew === 'true' ? true : false,
                'start_date' => $request->startDate ? date('Y-m-d', strtotime($request->startDate)) : null,
                'end_date' => $request->endDate ? date('Y-m-d', strtotime($request->endDate)) : null,
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file')[0];
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/rti';
                $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                SpRtiNotice::whereId($data->id)->update([
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

    // -----------------------------------------------

    public function update(RtiNoticeRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $data = SpRtiNotice::findOrFail($id);

            SpRtiNotice::whereId($id)->update([
                'notice_no' => trim($request->noticeNo),
                'subject' => trim($request->subject),
                'is_new' => $request->isNew === 'true' ? true : false,
                'start_date' => $request->startDate ? date('Y-m-d', strtotime($request->startDate)) : null,
                'end_date' => $request->endDate ? date('Y-m-d', strtotime($request->endDate)) : null,
            ]);

            if ($request->hasFile('file') && $request->file('file')[0]->getSize() > 0) {
                $file = $request->file('file')[0];
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/rti';
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

                SpRtiNotice::whereId($data->id)->update([
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

    // -----------------------------------------------

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $data = SpRtiNotice::findOrFail($id);
            $filePath = $data->file_path ? str_replace('/storage', '', $data->file_path) : null;

            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            SpRtiNotice::where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'deleted'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while processing your request.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -----------------------------------------------

    public function activate(Request $request, string $id)
    {
        SpRtiNotice::whereId($id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

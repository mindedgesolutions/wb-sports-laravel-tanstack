<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceTenderRequest;
use App\Models\ServiceTender;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ServiceTenderController extends Controller
{
    public function index()
    {
        $data = ServiceTender::orderBy('tender_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -----------------------------------------

    public function store(ServiceTenderRequest $request)
    {
        $data = ServiceTender::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'tender_date' => $request->tenderDate ? date('Y-m-d', strtotime($request->tenderDate)) : null,
        ]);

        if ($request->hasFile('file') && $request->file('file')[0]->getSize() > 0) {
            $file = $request->file('file')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/tenders';
            $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            ServiceTender::whereId($data->id)->update([
                'file_name' => $fileOriginalName,
                'file_path' => Storage::url($filePath),
            ]);
        }
        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -----------------------------------------

    public function update(ServiceTenderRequest $request, $id)
    {
        $data = ServiceTender::findOrFail($id);

        ServiceTender::whereId($id)->update([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'tender_date' => $request->tenderDate ? date('Y-m-d', strtotime($request->tenderDate)) : null,
        ]);

        if ($request->hasFile('file') && $request->file('file')[0]->getSize() > 0) {
            $file = $request->file('file')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/tenders';
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

            ServiceTender::whereId($data->id)->update([
                'file_name' => $fileOriginalName,
                'file_path' => Storage::url($filePath),
            ]);
        }
    }

    // -----------------------------------------

    public function destroy(string $id)
    {
        //
    }

    // -----------------------------------------

    public function activate(Request $request, $id)
    {
        ServiceTender::whereId($id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

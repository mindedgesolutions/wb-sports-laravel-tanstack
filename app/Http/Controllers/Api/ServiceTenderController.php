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
        $search = request()->query('search');

        $data = ServiceTender::when($search, function ($query, $search) {
            $query->where('name', 'ilike', "%{$search}%");
        })
            ->orderBy('tender_date', 'desc')
            ->orderBy('id', 'desc')
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

    public function store(ServiceTenderRequest $request)
    {
        $data = ServiceTender::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'tender_date' => $request->tenderDate ?? null,
        ]);

        if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
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

    public function update(ServiceTenderRequest $request, String $id)
    {
        $data = ServiceTender::findOrFail($id);

        ServiceTender::whereId($id)->update([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'tender_date' => $request->tenderDate ?? null,
        ]);

        if ($request->hasFile('newFile') && $request->file('newFile')->getSize() > 0) {
            $file = $request->file('newFile');
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
        $data = ServiceTender::findOrFail($id);
        $filePath = str_replace('/storage', '', $data->file_path);

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        ServiceTender::where('id', $id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -----------------------------------------

    public function toggle(Request $request, String $id)
    {
        ServiceTender::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

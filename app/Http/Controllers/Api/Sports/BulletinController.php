<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpBulletin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BulletinController extends Controller
{
    public function index()
    {
        $data = SpBulletin::orderBy('event_date')->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function store(Request $request)
    {
        $data = $request->all();
        $data['eventDate'] = $request->eventDate
            ? Date::createFromFormat('d/m/Y', $request->eventDate)
            : null;
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|max:255',
            'eventDate' => 'nullable|before_or_equal:today',
            'file' => 'required|array',
            'file.*' => 'required|file|max:10240',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = SpBulletin::create([
            'name' => $request->name ? trim($request->name) : null,
            'slug' => $request->name ? Str::slug($request->name) : null,
            'event_date' => $data['eventDate'] ? $data['eventDate']->format('Y-m-d') : null,
        ]);

        if ($request->hasFile('file') && $request->file('file')[0]->getSize() > 0) {
            $file = $request->file('file')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/bulletins';

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            SpBulletin::whereId($data->id)->update(['file_path' => Storage::url($filePath)]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------------------

    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $data['eventDate'] = $request->eventDate
            ? Date::createFromFormat('d/m/Y', $request->eventDate)
            : null;
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|max:255',
            'eventDate' => 'nullable|before_or_equal:today',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = SpBulletin::findOrFail($id);

        SpBulletin::whereId($id)->update([
            'name' => $request->name ? trim($request->name) : null,
            'slug' => $request->name ? Str::slug($request->name) : null,
            'event_date' => $data['eventDate'] ? $data['eventDate']->format('Y-m-d') : null,
        ]);

        if ($request->hasFile('file') && $request->file('file')[0]->getSize() > 0) {
            $file = $request->file('file')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/sports/bulletins';

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

            SpBulletin::whereId($id)->update(['file_path' => Storage::url($filePath)]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------------------

    public function destroy(string $id)
    {
        $data = SpBulletin::findOrFail($id);

        if ($data->file_path) {
            $deletePath = str_replace('/storage', '', $data->file_path);

            if (Storage::disk('public')->exists($deletePath)) {
                Storage::disk('public')->delete($deletePath);
            }
        }

        SpBulletin::whereId($id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function activate(Request $request, $id)
    {
        SpBulletin::whereId($id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

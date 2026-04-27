<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\YouthHostelRequest;
use App\Models\YouthHostel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class YouthHostelController extends Controller
{
    public function index()
    {
        $data = YouthHostel::orderBy('id', 'desc')->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -------------------------------------------------

    public function store(YouthHostelRequest $request)
    {
        if ($request->hasFile('cover') && $request->file('cover')[0]->getSize() > 0) {
            $file = $request->file('cover')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/hostels';

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $filePath = $file->storeAs($directory, $filename, 'public');
        } else {
            $filePath = null;
        }

        YouthHostel::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address' => trim($request->address),
            'phone_1' => $request->phone1 ? trim($request->phone1) : null,
            'phone_2' => $request->phone2 ? trim($request->phone2) : null,
            'email' => $request->email ? trim($request->email) : null,
            'accommodation' => $request->accommodation ? trim($request->accommodation) : null,
            'how_to_reach' => $request->howtoReach ? trim($request->howtoReach) : null,
            'railway_station' => trim($request->railwayStation),
            'bus_stop' => $request->busStop ? trim($request->busStop) : null,
            'airport' => $request->airport ? trim($request->airport) : null,
            'road_network' => $request->network ? trim($request->network) : null,
            'remarks' => $request->remarks ? trim($request->remarks) : null,
            'district_id' => $request->district,
            'hostel_img' => $filePath ? Storage::url($filePath) : null,
            'added_by' => Auth::id(),
            'uuid' => Str::uuid(),
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------------------------

    public function show($uuid)
    {
        $data = YouthHostel::where('uuid', $uuid)->first();

        if (!$data) {
            return response()->json(['message' => 'Youth hostel not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------------

    public function youthHostelUpdate(YouthHostelRequest $request, string $id)
    {
        $data = YouthHostel::findOrFail($id);

        if ($request->hasFile('cover') && $request->file('cover')[0]->getSize() > 0) {
            if ($data) {
                $deletePath = str_replace('/storage', '', $data->hostel_img);

                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            $file = $request->file('cover')[0];
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/hostels';

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $filePath = $file->storeAs($directory, $filename, 'public');
        } else {
            $filePath = null;
        }

        $data->update([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address' => trim($request->address),
            'phone_1' => $request->phone1 ? trim($request->phone1) : null,
            'phone_2' => $request->phone2 ? trim($request->phone2) : null,
            'email' => $request->email ? trim($request->email) : null,
            'accommodation' => $request->accommodation ? trim($request->accommodation) : null,
            'how_to_reach' => $request->howtoReach ? trim($request->howtoReach) : null,
            'railway_station' => trim($request->railwayStation),
            'bus_stop' => $request->busStop ? trim($request->busStop) : null,
            'airport' => $request->airport ? trim($request->airport) : null,
            'road_network' => $request->network ? trim($request->network) : null,
            'remarks' => $request->remarks ? trim($request->remarks) : null,
            'district_id' => $request->district,
            'hostel_img' => $filePath ? Storage::url($filePath) : $data->hostel_img ?? null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------------

    public function destroy(string $id)
    {
        $data = YouthHostel::findOrFail($id);

        if ($data) {
            $deletePath = str_replace('/storage', '', $data->hostel_img);

            if (Storage::disk('public')->exists($deletePath)) {
                Storage::disk('public')->delete($deletePath);
            }

            $data->delete();
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------------

    public function activate(Request $request, $id)
    {
        YouthHostel::where('id', $id)->update([
            'is_active' => $request->is_active,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\YouthHostelRequest;
use App\Models\YouthHostel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class YouthHostelController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = YouthHostel::searchHostel($search)
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

    // -------------------------------------------------

    public function store(YouthHostelRequest $request)
    {
        $data = YouthHostel::create([
            'district_id' => $request->districtId,
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address' => trim($request->address),
            'phone_1' => $request->phone_1 ? trim($request->phone_1) : null,
            'phone_2' => $request->phone_2 ? trim($request->phone_2) : null,
            'email' => $request->email ? trim($request->email) : null,
            'accommodation' => $request->accommodation ? trim($request->accommodation) : null,
            'how_to_reach' => $request->reach ? trim($request->reach) : null,
            'railway_station' => trim($request->trainStation),
            'bus_stop' => $request->busStop ? trim($request->busStop) : null,
            'airport' => $request->airport ? trim($request->airport) : null,
            'road_network' => $request->network ? trim($request->network) : null,
            'remarks' => $request->remarks ? trim($request->remarks) : null,
            'added_by' => Auth::id(),
            'uuid' => Str::uuid(),
        ]);

        if ($request->hasFile('newImg') && $request->file('newImg')->getSize() > 0) {
            $file = $request->file('newImg');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/hostels';

            if ($data->hostel_img) {
                $deletePath = str_replace('/storage', '', $data->hostel_img);
                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            $data->hostel_img = Storage::url($filePath);
            $data->save();
        } else {
            $filePath = null;
        }
        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------------------------

    public function show(String $id)
    {
        $data = YouthHostel::findOrFail($id);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------------

    public function update(YouthHostelRequest $request, string $id)
    {
        $data = YouthHostel::findOrFail($id);

        YouthHostel::whereId($id)->update([
            'district_id' => $request->districtId,
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'address' => trim($request->address),
            'phone_1' => $request->phone_1 ? trim($request->phone_1) : null,
            'phone_2' => $request->phone_2 ? trim($request->phone_2) : null,
            'email' => $request->email ? trim($request->email) : null,
            'accommodation' => $request->accommodation ? trim($request->accommodation) : null,
            'how_to_reach' => $request->reach ? trim($request->reach) : null,
            'railway_station' => trim($request->trainStation),
            'bus_stop' => $request->busStop ? trim($request->busStop) : null,
            'airport' => $request->airport ? trim($request->airport) : null,
            'road_network' => $request->network ? trim($request->network) : null,
            'remarks' => $request->remarks ? trim($request->remarks) : null,
        ]);

        if ($request->hasFile('newImg') && $request->file('newImg')->getSize() > 0) {
            if ($data) {
                $deletePath = str_replace('/storage', '', $data->hostel_img);

                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
            }

            $file = $request->file('newImg');
            $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
            $directory = 'uploads/services/hostels';

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            $filePath = $file->storeAs($directory, $filename, 'public');

            YouthHostel::whereId($id)->update([
                'hostel_img' => Storage::url($filePath)
            ]);
        }
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

    public function toggle(Request $request, String $id)
    {
        YouthHostel::where('id', $id)->update([
            'is_active' => $request->checked,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\SpAdvisoryCommittRequest;
use App\Models\SpAdvisoryCommittee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdvisoryCommitteeController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpAdvisoryCommittee::join('sp_wbs_council_designations as swcd', 'sp_advisory_committees.designation_id', '=', 'swcd.id')
            ->when($search, function ($query, $search) {
                $typeSlug = Str::slug($search);

                $query->where('sp_advisory_committees.name', 'ILIKE', "%{$search}%")
                    ->orWhere('swcd.designation', 'ILIKE', "%{$search}%")
                    ->orWhere('swcd.type', 'ILIKE', "%{$typeSlug}%");
            })
            ->select(
                'sp_advisory_committees.*',
                'swcd.type as type',
                'swcd.designation as designation_name',
                'swcd.weight'
            )
            ->orderBy('swcd.type', 'asc')
            ->orderBy('swcd.weight', 'asc')
            ->orderBy('sp_advisory_committees.name')
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

    // ---------------------------------------------

    public function store(SpAdvisoryCommittRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = SpAdvisoryCommittee::create([
                'designation_id' => $request->designation,
                'name' => trim($request->name),
                'slug' => Str::slug($request->name) . $request->designation,
                'designation_label' => $request->designationLabel ? trim($request->designationLabel) : null,
                'address' => $request->address ? trim($request->address) : null,
                'phone' => $request->phone ?? null,
                'email' => $request->email ?? null,
                'fax' => $request->fax ?? null,
            ]);

            if ($request->hasFile('newImg') && $request->file('newImg')->getSize() > 0) {
                $file = $request->file('newImg');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/committee';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');
                $data->image_path = Storage::url($filePath);
                $data->save();
            }

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while processing your request.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ---------------------------------------------

    public function update(SpAdvisoryCommittRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $current = SpAdvisoryCommittee::whereId($id)->first();

            $data = SpAdvisoryCommittee::whereId($id)->update([
                'designation_id' => $request->designation,
                'name' => trim($request->name),
                'slug' => Str::slug($request->name) . $request->designation,
                'designation_label' => $request->designationLabel ?? null,
                'address' => $request->address ?? null,
                'phone' => $request->phone ?? null,
                'email' => $request->email ?? null,
                'fax' => $request->fax ?? null,
            ]);

            if ($request->hasFile('newImg') && $request->file('newImg')->getSize() > 0) {
                $file = $request->file('newImg');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/sports/committee';
                if ($data) {
                    $deletePath = str_replace('/storage', '', $current->image_path);

                    if (Storage::disk('public')->exists($deletePath)) {
                        Storage::disk('public')->delete($deletePath);
                    }
                }
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                SpAdvisoryCommittee::whereId($id)->update([
                    'image_path' => Storage::url($filePath),
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while processing your request.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ---------------------------------------------

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $data = SpAdvisoryCommittee::findOrFail($id);
            $filePath = str_replace('/storage', '', $data->image_path);

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            SpAdvisoryCommittee::where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ---------------------------------------------

    public function toggle(Request $request, $id)
    {
        SpAdvisoryCommittee::where('id', $id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VocationalTraining;
use App\Models\VocationalTrainingCentre;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class VocationalTrainingController extends Controller
{
    public function indexContent()
    {
        $content = VocationalTraining::orderBy('show_order')->orderBy('id')->paginate(10);

        return response()->json(['data' => $content], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function storeContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                if (VocationalTraining::where('slug', $slug)->exists()) {
                    $fail('Scheme exists');
                }
            }],
        ], [
            '*.required' => ':Attribute is required',
        ], [
            'content' => 'content',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $slug = Str::slug($request->content);
            VocationalTraining::create([
                'content' => $request->content,
                'slug' => $slug,
            ]);

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -------------------------------------------

    public function activateContent(Request $request, string $id)
    {
        VocationalTraining::where('id', $id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function updateContent(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ], [
            '*.required' => ':Attribute is required',
        ], [
            'content' => 'content',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            VocationalTraining::where('id', $id)->update([
                'content' => $request->content,
            ]);

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -------------------------------------------

    public function destroyContent(string $id)
    {
        VocationalTraining::where('id', $id)->delete();
        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function vocSchemeAll()
    {
        $schemes = VocationalTraining::orderBy('show_order')->orderBy('id')->get();

        return response()->json(['data' => $schemes], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function vocSchemeSetOrder(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            VocationalTraining::where('id', $value['id'])->update(['show_order' => $key]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function indexCentre()
    {
        $centreList =
            VocationalTrainingCentre::join('districts', 'vocational_training_centres.district_id', '=', 'districts.id')
            ->select('vocational_training_centres.*', 'districts.name as district_name')
            ->orderBy('districts.name')
            ->paginate(10);

        return response()->json(['data' => $centreList], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function storeCentre(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'district' => 'required',
            'name' => ['required', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                if (VocationalTrainingCentre::where('slug', $slug)->exists()) {
                    $fail('Centre exists');
                }
            }],
            'address' => 'required',
            'phone' => 'nullable',
        ], [
            '*.required' => ':Attribute is required',
        ], [

            'district' => 'district',
            'name' =>  'centre name',
            'address'  => 'address',
            'phone' => 'phone',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            VocationalTrainingCentre::create([
                'district_id' => $request->district,
                'name_of_centre' => trim($request->name),
                'slug' => Str::slug($request->name),
                'address'  => trim($request->address),
                'phone' => $request->phone ? trim($request->phone) : null,
            ]);

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -------------------------------------------

    public function activateCentre(Request $request, string $id)
    {
        VocationalTrainingCentre::where('id', $id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------------------

    public function updateCentre(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'district' => 'required',
            'name' => ['required', function ($attribute, $value, $fail) use ($id) {
                $slug = Str::slug($value);
                if (VocationalTrainingCentre::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $fail('Centre exists');
                }
            }],
            'address' => 'required',
            'phone' => 'nullable',
        ], [
            '*.required' => ':Attribute is required',
        ], [

            'district' => 'district_id',
            'name' =>  'name_of_centre',
            'address'  => 'address',
            'phone' => 'phone',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            VocationalTrainingCentre::where('id', $id)->update([
                'district_id' => $request->district,
                'name_of_centre' => trim($request->name),
                'slug' => Str::slug($request->name),
                'address'  => trim($request->address),
                'phone' => $request->phone ? trim($request->phone) : null,
            ]);

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -------------------------------------------

    public function destroyCentre(string $id)
    {
        VocationalTrainingCentre::where('id', $id)->delete();
        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

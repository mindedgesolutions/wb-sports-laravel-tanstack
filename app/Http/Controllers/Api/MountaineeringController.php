<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MountainGeneralBody;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MountaineeringController extends Controller
{
    // General body methods start -------------------------------

    public function gbIndex()
    {
        $data = MountainGeneralBody::where('organisation', 'services')
            ->orderBy('show_order')
            ->paginate(10);

        return response()->json(['members' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function gbStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'designation' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'desc' => 'required|string|max:255'
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute must not exceed :max characters'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slug = Str::slug($request->name);
        $check = MountainGeneralBody::where('slug', $slug)->first();
        if ($check) {
            return response()->json(['errors' => ['name' => ['Member already exists']]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        MountainGeneralBody::create([
            'designation' => trim($request->designation) ?? null,
            'name' => trim($request->name),
            'description' => trim($request->desc),
            'organisation' => 'services',
            'added_by' => Auth::id(),
            'slug' => $slug,
        ]);

        return response()->json(['message' => 'General body created successfully'], Response::HTTP_CREATED);
    }

    // --------------------------------

    public function gbUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'designation' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'desc' => 'required|string|max:255'
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute must not exceed :max characters'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slug = Str::slug($request->name);
        $check = MountainGeneralBody::where('slug', $slug)
            ->where('id', '!=', $id)
            ->first();

        if ($check) {
            return response()->json(['errors' => ['name' => ['Member already exists']]], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        MountainGeneralBody::where('id', $id)->update([
            'designation' => $request->designation ?? null,
            'name' => $request->name,
            'description' => $request->desc,
            'slug' => $slug,
        ]);

        return response()->json(['message' => 'General body updated successfully'], Response::HTTP_OK);
    }

    // --------------------------------

    public function gbDestroy($id)
    {
        MountainGeneralBody::where('id', $id)->delete();

        return response()->json(['message' => 'General body deleted successfully'], Response::HTTP_OK);
    }

    // --------------------------------

    public function gbMembersAll()
    {
        $data = MountainGeneralBody::where('organisation', 'services')
            ->orderBy('show_order')
            ->get();

        return response()->json(['members' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function gbMembersSetOrder(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            MountainGeneralBody::where('id', $value['id'])->update(['show_order' => $key]);
        }

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // General body methods end -------------------------------

    // Training calendar methods start -------------------------------

    public function tcIndex() {}

    // --------------------------------

    public function tcStore(Request $request) {}

    // --------------------------------

    public function tcUpdate(Request $request, $id) {}

    // --------------------------------

    public function tcDestroy($id) {}

    // Training calendar methods end -------------------------------
}

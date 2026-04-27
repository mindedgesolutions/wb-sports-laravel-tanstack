<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpAudioVisual;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AudioVisualController extends Controller
{
    public function index()
    {
        $data = SpAudioVisual::orderBy('id', 'desc')->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ----------------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'videoLink' => ['required', 'url', 'regex:/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)[\w\-]{11}$/'],
            'title' => 'nullable|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        SpAudioVisual::create([
            'video_link' => $request->videoLink,
            'title' => $request->title ? trim($request->title) : null
        ]);

        return response()->json(['data' => 'success'], Response::HTTP_CREATED);
    }

    // ----------------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'videoLink' => ['required', 'url', 'regex:/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)[\w\-]{11}$/'],
            'title' => 'nullable|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        SpAudioVisual::whereId($id)->update([
            'video_link' => $request->videoLink,
            'title' => $request->title ? trim($request->title) : null
        ]);

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }

    // ----------------------------------------------------

    public function destroy(string $id)
    {
        SpAudioVisual::whereId($id)->delete();

        return response()->json(['data' => 'success'], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpAudioVisual;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AudioVisualController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpAudioVisual::when($search, function ($query, $search) {
            $query->where('title', 'ILIKE', "%{$search}%");
        })
            ->orderBy('id', 'desc')
            ->paginate(12);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total()
            ]
        ], Response::HTTP_OK);
    }

    // ----------------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => [
                'required',
                'url',
                'regex:/^(https?:\/\/)?(www\.|m\.)?(youtube\.com\/(watch\?v=|embed\/|shorts\/)|youtu\.be\/)[A-Za-z0-9_-]{11}([&?].*)?$/',
            ],
            'title' => 'nullable|max:255',
        ], [
            '*.required' => ':Attribute is required',
            '*.url' => 'Please enter a valid YouTube video URL',
            '*.regex' => 'Please enter a valid YouTube video URL',

        ], [
            'url' => 'YouTube URL'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        SpAudioVisual::create([
            'video_link' => $request->url,
            'title' => $request->title ? trim($request->title) : null
        ]);

        return response()->json(['data' => 'success'], Response::HTTP_CREATED);
    }

    // ----------------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'url' => [
                'required',
                'url',
                'regex:/^(https?:\/\/)?(www\.|m\.)?(youtube\.com\/(watch\?v=|embed\/|shorts\/)|youtu\.be\/)[A-Za-z0-9_-]{11}([&?].*)?$/',
            ],
            'title' => 'nullable|max:255',
        ], [
            '*.required' => ':Attribute is required',
            '*.url' => 'Please enter a valid YouTube video URL',
            '*.regex' => 'Please enter a valid YouTube video URL',

        ], [
            'url' => 'YouTube URL'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        SpAudioVisual::whereId($id)->update([
            'video_link' => $request->url,
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

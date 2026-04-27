<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Models\SpSportsEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SportsEventController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpSportsEvent::when($search, function ($query, $search) {
            $query->where('title', 'ILIKE', "%{$search}%");
        })
            ->orderBy('event_date', 'desc')
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

    // ------------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', function ($attribute, $value, $fail) {
                $inputSlug = Str::slug($value);
                if (SpSportsEvent::where('slug', $inputSlug)->exists()) {
                    $fail('Event exists');
                }
            }],
            'eventDate' => 'required|before_or_equal:today',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        SpSportsEvent::create([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'event_date' => $request->eventDate ? $request->eventDate : null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // ------------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', function ($attribute, $value, $fail) use ($id) {
                $inputSlug = Str::slug($value);
                if (SpSportsEvent::where('slug', $inputSlug)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    $fail('Event exists');
                }
            }],
            'eventDate' => 'required|before_or_equal:today',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        SpSportsEvent::whereId($id)->update([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'event_date' => $request->eventDate ? $request->eventDate : null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // ------------------------------------------------

    public function destroy(string $id)
    {
        SpSportsEvent::whereId($id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // ------------------------------------------------

    public function toggle(Request $request, $id)
    {
        $event = SpSportsEvent::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

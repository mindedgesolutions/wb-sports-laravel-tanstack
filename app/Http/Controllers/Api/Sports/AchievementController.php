<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\SpAchievementRequest;
use App\Models\SpAchievement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class AchievementController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpAchievement::when($search, function ($query, $search) {
            $query->where('title', 'ILIKE', "%{$search}%")
                ->orWhere('description', 'ILIKE', "%{$search}%");
        })
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

    // --------------------------------------------

    public function store(SpAchievementRequest $request)
    {
        SpAchievement::create([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'description' => $request->description ? trim($request->description) : null,
            'achievement_date' => $request->achievementDate ?? null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // --------------------------------------------

    public function update(SpAchievementRequest $request, string $id)
    {
        SpAchievement::whereId($id)->update([
            'title' => trim($request->title),
            'slug' => Str::slug($request->title),
            'description' => $request->description ? trim($request->description) : null,
            'achievement_date' => $request->achievementDate,
        ]);

        return response()->json('success', Response::HTTP_OK);
    }

    // --------------------------------------------

    public function destroy(string $id)
    {
        SpAchievement::whereId($id)->delete();

        return response()->json('success', Response::HTTP_OK);
    }

    // --------------------------------------------

    public function toggle(Request $request, string $id)
    {
        $achievement = SpAchievement::whereId($id)->update([
            'is_active' => $request->checked,
        ]);

        return response()->json('success', Response::HTTP_OK);
    }
}

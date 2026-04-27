<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\SpPlayersAchievementRequest;
use App\Models\SpPlayersAchievement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PlayersAchievementController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = SpPlayersAchievement::when($search, function ($query, $search) {
            $query->where('sport', 'ILIKE', "%{$search}%")
                ->orWhere('name', 'ILIKE', "%{$search}%")
                ->orWhere('description', 'ILIKE', "%{$search}%");
        })
            ->orderBy('sport')
            ->orderBy('name')
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

    // --------------------------------------------------

    public function store(SpPlayersAchievementRequest $request)
    {
        SpPlayersAchievement::create([
            'sport' => $request->sport,
            'name' => trim($request->name),
            'slug' => Str::slug($request->name) . '-' . $request->sport,
            'description' => trim($request->description),
            'achievement_date' => $request->achievementDate ?? null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // --------------------------------------------------

    public function update(SpPlayersAchievementRequest $request, string $id)
    {
        SpPlayersAchievement::whereId($id)->update([
            'sport' => $request->sport,
            'name' => trim($request->name),
            'slug' => Str::slug($request->name) . '-' . $request->sport,
            'description' => trim($request->description),
            'achievement_date' => $request->achievementDate ?? null,
        ]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------------

    public function destroy(string $id)
    {
        SpPlayersAchievement::whereId($id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------------------------

    public function toggle(Request $request, $id)
    {
        SpPlayersAchievement::whereId($id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}

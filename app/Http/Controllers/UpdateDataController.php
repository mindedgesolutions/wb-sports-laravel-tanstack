<?php

namespace App\Http\Controllers;

use App\Imports\AdvCommitteeImport;
use App\Imports\AnnouncementsImport;
use App\Imports\DistrictOfficeImport;
use App\Imports\EventsImport;
use App\Imports\HostelsImport;
use App\Imports\PlayerAchievementImport;
use App\Imports\SportsPersonnelImport;
use App\Models\DistrictBlockOffice;
use App\Models\SpAdvisoryCommittee;
use App\Models\SpAnnouncement;
use App\Models\SpPlayersAchievement;
use App\Models\SpSportsEvent;
use App\Models\SpSportsPersonnel;
use App\Models\YouthHostel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UpdateDataController extends Controller
{
    public function uploadSportsPersonnel(Request $request)
    {
        SpSportsPersonnel::truncate();

        Excel::import(new SportsPersonnelImport, $request->file('file'));

        return response()->json([
            'message' => 'Import successful.',
        ]);
    }

    public function uploadSportsEvents(Request $request)
    {
        SpSportsEvent::truncate();

        Excel::import(new EventsImport, $request->file('file'));

        return response()->json([
            'message' => 'Import successful.',
        ]);
    }

    public function uploadAdvisoryCommittee(Request $request)
    {
        SpAdvisoryCommittee::truncate();

        Excel::import(new AdvCommitteeImport, $request->file('file'));

        return response()->json([
            'message' => 'Import successful.',
        ]);
    }

    public function uploadAnnouncements(Request $request)
    {
        SpAnnouncement::truncate();

        Excel::import(new AnnouncementsImport, $request->file('file'));

        return response()->json([
            'message' => 'Import successful.',
        ]);
    }

    public function uploadPlayerAchievements(Request $request)
    {
        SpPlayersAchievement::truncate();

        Excel::import(new PlayerAchievementImport, $request->file('file'));

        return response()->json([
            'message' => 'Import successful.',
        ]);
    }

    public function uploadHostels(Request $request)
    {
        YouthHostel::truncate();

        Excel::import(new HostelsImport, $request->file('file'));

        return response()->json([
            'message' => 'Import successful.',
        ]);
    }

    public function uploadDistrictOffices(Request $request)
    {
        DistrictBlockOffice::truncate();

        Excel::import(new DistrictOfficeImport, $request->file('file'));

        return response()->json([
            'message' => 'Import successful.',
        ]);
    }
}

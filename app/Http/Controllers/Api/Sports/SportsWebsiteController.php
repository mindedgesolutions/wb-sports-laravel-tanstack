<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sports\StadiumResource;
use App\Models\SpAchievement;
use App\Models\SpAdvertisement;
use App\Models\SpAdvisoryCommittee;
use App\Models\SpAmphanPhoto;
use App\Models\SpAnnouncement;
use App\Models\SpAssociation;
use App\Models\SpAssocSite;
use App\Models\SpAudioVisual;
use App\Models\SpAward;
use App\Models\SpBulletin;
use App\Models\SpContact;
use App\Models\SpFifa;
use App\Models\SpHomepageSlider;
use App\Models\SpKeyPersonnel;
use App\Models\SpNewsScroll;
use App\Models\SpOrganisationStructure;
use App\Models\SpPhoto;
use App\Models\SpPhotoGallery;
use App\Models\SpPlayersAchievement;
use App\Models\SpRtiNotice;
use App\Models\SpSportPolicy;
use App\Models\SpSportsEvent;
use App\Models\SpSportsPersonnel;
use App\Models\SpStadium;
use App\Models\SpWbsCouncilDesignation;
use Illuminate\Http\Response;

class SportsWebsiteController extends Controller
{
    public function getHomepageSlider()
    {
        $data = SpHomepageSlider::where('is_active', true)
            ->orderBy('show_order')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getKeyPersonnel()
    {
        $data = SpKeyPersonnel::where('is_active', true)->orderBy('show_order')->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getSportsPersonnel()
    {
        $data = SpSportsPersonnel::where('is_active', true)->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getAchievementsAll()
    {
        $data = SpAchievement::where('is_active', true)
            ->orderBy('show_order')
            ->orderBy('achievement_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getOrgStructureAll()
    {
        $data = SpOrganisationStructure::where('is_active', true)
            ->orderBy('show_order')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getWbsDesignations($type)
    {
        $data = SpWbsCouncilDesignation::where('type', $type)
            ->where('is_active', true)
            ->orderBy('weight')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getAdvisoryBoard()
    {
        $data = SpAdvisoryCommittee::with('designation')
            ->whereHas('designation', function ($query) {
                $query->where('type', 'advisory-board');
            })
            ->where('is_active', true)
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getWorkingCommittee()
    {
        $data = SpAdvisoryCommittee::with('designation')
            ->whereHas('designation', function ($query) {
                $query->where('type', 'working-committee');
            })
            ->where('is_active', true)
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getPlayersAchievementSingle($slug)
    {
        $data = SpPlayersAchievement::where('sport', $slug)
            ->where('is_active', true)
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ------------------------------------------------

    public function getSportsEventsAll()
    {
        $data = SpSportsEvent::orderBy('event_date')
            ->where('is_active', true)
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getAnnouncementsAll($type)
    {
        $data = SpAnnouncement::where('type', $type)
            ->where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getAnnouncementsLtd($type, $count)
    {
        $data = SpAnnouncement::where('type', $type)
            ->where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->limit($count)
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getAwardsAll()
    {
        $data = SpAward::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getStadiumsAll()
    {
        $data = SpStadium::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    //  --------------------------------------------

    public function getStadiumInfo($slug)
    {
        $data = SpStadium::where('slug', $slug)->first();

        return StadiumResource::make($data);
    }

    // --------------------------------------------

    public function imagesLanding($count)
    {
        $data = SpPhoto::orderBy('id', 'desc')->limit($count)->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function galleryAll()
    {
        $data = SpPhotoGallery::withCount('photos')
            ->where('category', 'photo')
            ->where('is_active', true)
            ->orderBy('event_date')
            ->orderBy('title')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function gallerySingle($slug)
    {
        $data = SpPhotoGallery::with('photos')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function audioVisualsAll()
    {
        $data = SpAudioVisual::orderBy('id', 'desc')->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getAmphanPhotos()
    {
        $data = SpAmphanPhoto::orderBy('id', 'desc')->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getBulletinsAll()
    {
        $data = SpBulletin::where('is_active', true)
            ->orderBy('event_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getAdvertisementsAll()
    {
        $data = SpAdvertisement::where('is_active', true)
            ->orderBy('ad_date', 'desc')
            ->orderBy('title')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getAssociationsAll()
    {
        $data = SpAssociation::where('is_active', true)
            ->orderBy('name')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function fifaAll()
    {
        $data = SpFifa::withCount('photos')
            ->with('firstPhoto')
            ->where('is_active', true)
            ->orderBy('event_date')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function fifaSingle($slug)
    {
        $data = SpFifa::with('photos')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function sportsPoliciesAll()
    {
        $data = SpSportPolicy::where('is_active', true)
            ->orderBy('name')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function assocSitesAll()
    {
        $data = SpAssocSite::orderBy('id')->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------------------

    public function getRtiNotices()
    {
        $data = SpRtiNotice::where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ---------------------------------------------

    public function getContactsAll()
    {
        $data = SpContact::orderBy('show_order')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // ---------------------------------------------

    public function galleryImagesLtd($count)
    {
        $data = SpPhoto::orderBy('id', 'desc')
            ->limit($count)
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function newsScroller()
    {
        $news = SpNewsScroll::orderBy('news_date', 'desc')
            ->orderBy('id', 'desc')
            ->where('is_active', true)
            ->get();

        return response()->json(['data' => $news], Response::HTTP_OK);
    }
}

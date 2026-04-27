<?php

namespace App\Http\Controllers\Api;

use App\Exports\CompCentresExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\FairProgramResource;
use App\Models\CompCenter;
use App\Models\CompSyllabus;
use App\Models\CompTrainCourseDetail;
use App\Models\District;
use App\Models\FairProgramme;
use App\Models\FairProgrammeGallery;
use App\Models\FairProgrammGalleryImage;
use App\Models\MountainGeneralBody;
use App\Models\MountainTraining;
use App\Models\NewsEvent;
use App\Models\OrganisationChart;
use App\Models\ServiceTender;
use App\Models\VocationalTraining;
use App\Models\VocationalTrainingCentre;
use App\Models\YouthHostel;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelExcel;

class ServiceWebsiteController extends Controller
{
    public function districts()
    {
        $districts = District::orderBy('name')->get();

        return response()->json(['data' => $districts], Response::HTTP_OK);
    }

    // --------------------------------

    public function districtWiseBlockOffices()
    {
        $data = District::with('districtOffices')->orderBy('name')->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function computerCoursesAll()
    {
        $courses = CompTrainCourseDetail::where('is_active', true)->get();

        $syllabi = CompSyllabus::where('is_active', true)->get();

        return response()->json(['courses' => $courses, 'syllabi' => $syllabi], Response::HTTP_OK);
    }

    // --------------------------------

    public function photoGalleryAll()
    {
        $galleries = FairProgrammeGallery::where('show_in_gallery', true)
            ->with('cover')
            ->orderBy('programme_date', 'desc')
            ->get();

        return response()->json(['galleries' => $galleries], Response::HTTP_OK);
    }

    // --------------------------------

    public function photoGallerySingle($slug)
    {
        $gallery = FairProgrammeGallery::where('slug', $slug)->with(['images'])->first();

        if (!$gallery) {
            return response()->json(['message' => 'Gallery not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['gallery' => $gallery], Response::HTTP_OK);
    }

    // --------------------------------

    public function gbMembersAll()
    {
        $members = MountainGeneralBody::where('organisation', 'services')
            ->orderBy('show_order')
            ->get();

        return response()->json(['members' => $members], Response::HTTP_OK);
    }

    // --------------------------------

    public function fairProgrammesAll()
    {
        $fairs = FairProgramme::orderBy('created_at', 'desc')->get();

        return response()->json(['fairs' => $fairs], Response::HTTP_OK);
    }

    // --------------------------------

    public function fairProgrammesLtd($count)
    {
        $data = FairProgramme::orderBy('created_at', 'desc')
            ->limit($count)
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function fairProgrammesSingle($slug)
    {
        $fair = FairProgramme::where('slug', $slug)->first();

        if (!$fair) {
            return response()->json(['message' => 'Fair not found'], Response::HTTP_NOT_FOUND);
        }

        return FairProgramResource::make($fair);
    }

    // --------------------------------

    public function fairProgrammesGallery($slug, $gallerySlug)
    {
        $fair = FairProgramme::select('id', 'title')->where('slug', $slug)->first();

        if (!$fair->id) {
            return response()->json(['message' => 'Fair not found'], Response::HTTP_NOT_FOUND);
        }

        $gallery = FairProgrammeGallery::select('id', 'title', 'description')
            ->where('slug', $gallerySlug)
            ->where('program_id', $fair->id)
            ->first();

        if (!$gallery->id) {
            return response()->json(['message' => 'Gallery not found'], Response::HTTP_NOT_FOUND);
        }

        $images = FairProgrammGalleryImage::where('gallery_id', $gallery->id)->get();

        return response()->json([
            'images' => $images,
            'fairTitle' => $fair->title,
            'galleryTitle' => $gallery->title,
            'galleryDesc' => $gallery->description,
        ], Response::HTTP_OK);
    }

    // --------------------------------

    public function hostelsAll()
    {
        $hostels = YouthHostel::where('is_active', true)->orderBy('created_at', 'desc')->get();

        return response()->json(['hostels' => $hostels], Response::HTTP_OK);
    }

    // --------------------------------

    public function newsScroller()
    {
        $news = NewsEvent::orderBy('id', 'desc')->where('is_active', true)->limit(10)->get();

        return response()->json(['news' => $news], Response::HTTP_OK);
    }

    // --------------------------------

    public function newsEventsAll()
    {
        $news = NewsEvent::where('is_active', true)
            ->where('type', 'news')
            ->orderBy('event_year', 'desc')
            ->get();

        return response()->json(['news' => $news], Response::HTTP_OK);
    }

    // --------------------------------

    public function vocAll()
    {
        $schemes = VocationalTraining::orderBy('show_order')->get();
        $centres = VocationalTrainingCentre::join('districts', 'vocational_training_centres.district_id', '=', 'districts.id')
            ->select('vocational_training_centres.*', 'districts.name as district_name')
            ->orderBy('districts.name')
            ->get();

        return response()->json(['schemes' => $schemes, 'centres' => $centres], Response::HTTP_OK);
    }

    // --------------------------------

    public function organisationChart()
    {
        $data = OrganisationChart::where('is_active', true)
            ->orderBy('show_order')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function mountainTrainingsAll()
    {
        $data = MountainTraining::orderBy('id')->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function galleryImagesLtd($count)
    {
        $data = FairProgrammGalleryImage::orderBy('id', 'desc')
            ->limit($count)
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function eTendersAll()
    {
        $data = ServiceTender::orderBy('tender_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function compTrainingCentresAll()
    {
        $data = District::with(['compCenters' => function ($query) {
            $query->where('is_active', true);
        }])
            ->whereHas('compCenters', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // --------------------------------

    public function exportCompTrainingCentres($distId = null)
    {
        $data = CompCenter::join('districts', 'comp_centers.district_id', '=', 'districts.id')
            ->when($distId, function ($query) use ($distId) {
                return $query->where('district_id', $distId);
            })
            ->select('comp_centers.*', 'districts.name as district_name')
            ->where('comp_centers.is_active', true)
            ->orderBy('districts.name')
            ->orderBy('comp_centers.yctc_name')
            ->get();

        $send = [];

        foreach ($data as $item) {
            $send[] = [
                'district_name' => $item->district_name,
                'yctc_name' => $item->yctc_name,
                'yctc_code' => $item->yctc_code,
                'center_category' => $item->center_category,
                'address_line_1' => $item->address_line_1,
                'address_line_2' => $item->address_line_2,
                'address_line_3' => $item->address_line_3,
                'city' => $item->city,
                'pincode' => $item->pincode,
                'center_incharge_name' => $item->center_incharge_name,
                'center_incharge_mobile' => $item->center_incharge_mobile,
                'center_incharge_email' => $item->center_incharge_email,
                'center_owner_name' => $item->center_owner_name,
                'center_owner_mobile' => $item->center_owner_mobile,
            ];
        }

        return response()->json(['data' => $send], Response::HTTP_OK);
    }

    public function staticData()
    {
        $data = 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quae, cupiditate?';

        return response()->json(['data' => $data], Response::HTTP_OK);
    }
}

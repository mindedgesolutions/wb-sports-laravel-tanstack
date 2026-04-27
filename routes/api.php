<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CompCentreController;
use App\Http\Controllers\Api\CompSyllabusController;
use App\Http\Controllers\Api\ComputerTraining;
use App\Http\Controllers\Api\DistrictBlockOfficeController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\FairProgrammeController;
use App\Http\Controllers\Api\MountaineeringController;
use App\Http\Controllers\Api\MountainTrainingController;
use App\Http\Controllers\Api\NewsEventsController;
use App\Http\Controllers\Api\OrganisationChartController;
use App\Http\Controllers\Api\ServiceTenderController;
use App\Http\Controllers\Api\ServiceWebsiteController;
use App\Http\Controllers\Api\Sports\AchievementController;
use App\Http\Controllers\Api\Sports\AdvertisementController;
use App\Http\Controllers\Api\Sports\AdvisoryCommitteeController;
use App\Http\Controllers\Api\Sports\AmphanPhotoController;
use App\Http\Controllers\Api\Sports\AnnouncementsController;
use App\Http\Controllers\Api\Sports\AssociationController;
use App\Http\Controllers\Api\Sports\AssocSiteController;
use App\Http\Controllers\Api\Sports\AudioVisualController;
use App\Http\Controllers\Api\Sports\AwardsController;
use App\Http\Controllers\Api\Sports\BulletinController;
use App\Http\Controllers\Api\Sports\ContactController;
use App\Http\Controllers\Api\Sports\FeedbackController;
use App\Http\Controllers\Api\Sports\FifaController;
use App\Http\Controllers\Api\Sports\HomepageSliderController;
use App\Http\Controllers\Api\Sports\KeyPersonnelController;
use App\Http\Controllers\Api\Sports\NewsScrollController;
use App\Http\Controllers\Api\Sports\PhotoGalleryController;
use App\Http\Controllers\Api\Sports\PlayersAchievementController;
use App\Http\Controllers\Api\Sports\RtiNoticeController;
use App\Http\Controllers\Api\Sports\SpOrgStructureController;
use App\Http\Controllers\Api\Sports\SportPolicyController;
use App\Http\Controllers\Api\Sports\SportsEventController;
use App\Http\Controllers\Api\Sports\SportsPersonnelController;
use App\Http\Controllers\Api\Sports\SportsWebsiteController;
use App\Http\Controllers\Api\Sports\StadiumController;
use App\Http\Controllers\Api\Sports\WbsCouncilDesignationController;
use App\Http\Controllers\Api\VocationalTrainingController;
use App\Http\Controllers\Api\YouthHostelController;
use App\Http\Controllers\UpdateDataController;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login')->middleware('throttle:5, 1');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
    Route::post('refresh/{organisation}', 'refresh');
    Route::post('delete-one-time-token/{token}', 'deleteOneTimeToken');
    Route::get('captcha', 'generate');
});

Route::controller(UpdateDataController::class)->prefix('sports')->group(function () {
    Route::post('upload-sports-personnel', 'uploadSportsPersonnel');
    Route::post('upload-sports-events', 'uploadSportsEvents');
    Route::post('upload-advisory-committee', 'uploadAdvisoryCommittee');
    Route::post('upload-announcements', 'uploadAnnouncements');
    Route::post('upload-player-achievements', 'uploadPlayerAchievements');
    Route::post('upload-hostels', 'uploadHostels');
    Route::post('upload-district-offices', 'uploadDistrictOffices');
});

// Services app routes start -------------------------------

Route::middleware(['cookie.auth', 'auth:api'])->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('logout/{organisation}', 'logout');
        Route::get('me', 'me');
        Route::post('change-password', 'changePassword');
        Route::post('update', 'profileUpdate');
    });

    Route::apiResource('banners', BannerController::class)->except(['show', 'update']);
    Route::controller(BannerController::class)->prefix('banners')->group(function () {
        Route::post('update/{id}', 'bannerUpdate');
        Route::put('activate/{id}', 'activate');
    });

    Route::apiResource('com-training-courses', ComputerTraining::class)->except(['show']);
    Route::put('com-training-courses/activate/{id}', [ComputerTraining::class, 'activate']);

    Route::apiResource('comp-syllabus', CompSyllabusController::class)->except(['show', 'update']);
    Route::controller(CompSyllabusController::class)->prefix('comp-syllabus')->group(function () {
        Route::post('update/{id}', 'syllabusUpdate');
        Route::put('activate/{id}', 'activate');
    });

    Route::apiResource('comp-centres', CompCentreController::class)->except(['show']);
    Route::put('comp-centres/activate/{id}', [CompCentreController::class, 'activate']);

    Route::apiResource('vocatioanl-training-courses', VocationalTrainingController::class)->except(['show']);

    Route::controller(VocationalTrainingController::class)->prefix('vocational')->group(function () {
        Route::prefix('content')->group(function () {
            Route::post('store-content', 'storeContent');
            Route::put('activate-content/{id}', 'activateContent');
            Route::put('update-content/{id}', 'updateContent');
            Route::delete('destroy-content/{id}', 'destroyContent');
            Route::get('index-content', 'indexContent');
            Route::get('all', 'vocSchemeAll');
            Route::put('set-order', 'vocSchemeSetOrder');
        });
        Route::prefix('centre-list')->group(function () {
            Route::post('store-centre', 'storeCentre');
            Route::put('activate-centre/{id}', 'activateCentre');
            Route::put('update-centre/{id}', 'updateCentre');
            Route::delete('destroy-centre/{id}', 'destroyCentre');
            Route::get('index-centre', 'indexCentre');
        });
    });

    Route::controller(MountaineeringController::class)->prefix('mountain')->group(function () {
        Route::prefix('general-body')->group(function () {
            Route::get('list', 'gbIndex');
            Route::post('store', 'gbStore');
            Route::put('update/{id}', 'gbUpdate');
            Route::delete('delete/{id}', 'gbDestroy');
            Route::get('all', 'gbMembersAll');
            Route::put('set-order', 'gbMembersSetOrder');
        });
        Route::prefix('training-calendar')->group(function () {
            Route::get('list', 'tcIndex');
            Route::post('store', 'tcStore');
            Route::put('update/{id}', 'tcUpdate');
            Route::delete('delete/{id}', 'tcDestroy');
        });
    });

    Route::controller(FairProgrammeController::class)->prefix('fair-programme')->group(function () {
        Route::get('list', 'fpList');
        Route::post('store', 'fpStore');
        Route::get('edit/{uuid}', 'fpEdit');
        Route::post('update/{uuid}', 'fpUpdate');
        Route::delete('delete/{id}', 'fpDestroy');
        // ------------Gallery related starts ---------------
        Route::prefix('gallery')->group(function () {
            Route::post('store', 'fpGalleryStore');
            Route::post('update/{id}', 'fpGalleryUpdate');
            Route::delete('delete/{id}', 'fpGalleryDestroy');
            Route::delete('delete-image/{id}', 'fpGalleryImageDestroy');
            Route::put('show/{id}', 'fpShowInGallery');
        });
        // ------------Gallery related ends -----------------
    });

    Route::apiResource('district-block-offices', DistrictBlockOfficeController::class)->except(['show']);
    Route::put('district-block-offices/activate/{id}', [DistrictBlockOfficeController::class, 'activate']);

    Route::apiResource('youth-hostels', YouthHostelController::class)->except(['update']);
    Route::post('youth-hostels/update/{id}', [YouthHostelController::class, 'youthHostelUpdate']);
    Route::put('youth-hostels/activate/{id}', [YouthHostelController::class, 'activate']);

    Route::apiResource('news-events', NewsEventsController::class)->except(['show', 'update']);
    Route::put('news-events/activate/{id}', [NewsEventsController::class, 'activate']);
    Route::post('news-events/update/{id}', [NewsEventsController::class, 'updateNews']);

    Route::apiResource('org-chart', OrganisationChartController::class)->except(['show', 'update']);
    Route::controller(OrganisationChartController::class)->prefix('org-chart')->group(function () {
        Route::post('update/{id}', 'updateMember');
        Route::put('activate/{id}', 'activate');
        Route::get('all', 'orgChartAll');
        Route::put('set-order', 'orgChartSetOrder');
    });

    Route::apiResource('mountain-trainings', MountainTrainingController::class)->except(['show']);
    Route::controller(MountainTrainingController::class)->prefix('mountain-trainings')->group(function () {
        Route::post('update/{id}', 'update');
        Route::put('activate/{id}', 'activate');
    });

    Route::apiResource('e-tenders', ServiceTenderController::class)->except(['show', 'update']);
    Route::controller(ServiceTenderController::class)->prefix('e-tenders')->group(function () {
        Route::post('update/{id}', 'update');
        Route::put('activate/{id}', 'activate');
    });
});
// Services app routes end -------------------------------

// Services website routes start -------------------------------
Route::controller(ServiceWebsiteController::class)->prefix('services')->group(function () {
    Route::get('districts', 'districts');
    Route::get('district-wise-block-offices', 'districtWiseBlockOffices');
    Route::get('computer-courses-all', 'computerCoursesAll');
    Route::get('photo-galleries', 'photoGalleryAll');
    Route::get('photo-galleries/{slug}', 'photoGallerySingle');
    Route::get('fairs-programmes-ltd/{count}', 'fairProgrammesLtd');
    Route::get('fairs-programmes', 'fairProgrammesAll');
    Route::get('fair-programmes/{slug}', 'fairProgrammesSingle');
    Route::get('fair-programmes/{slug}/{gallerySlug}', 'fairProgrammesGallery');
    Route::get('gb-members', 'gbMembersAll');
    Route::get('youth-hostels', 'hostelsAll');
    Route::get('news-events/scroll', 'newsScroller');
    Route::get('news-events', 'newsEventsAll');
    Route::get('voc-all', 'vocAll');
    Route::get('organisation-chart', 'organisationChart');
    Route::get('mountain-trainings', 'mountainTrainingsAll');
    Route::get('gallery-images-ltd/{count}', 'galleryImagesLtd');
    Route::get('e-tenders', 'eTendersAll');
    Route::get('comp-training-centres', 'compTrainingCentresAll');
    Route::get('export-comp-training-centres/{distId?}', 'exportCompTrainingCentres');
    Route::get('static', 'staticData');
});
Route::get('banner/get', [BannerController::class, 'pageBanner']);
Route::get('com-training-courses/get', [ComputerTraining::class, 'courseList']);
// Services website routes end -------------------------------

// Sports app routes start -------------------------------
Route::middleware(['cookie.auth', 'auth:api'])->prefix('sports')->group(function () {
    Route::apiResource('homepage-sliders', HomepageSliderController::class)->except(['show']);
    Route::put('homepage-sliders/activate/{id}', [HomepageSliderController::class, 'activate']);

    Route::prefix('about-us')->group(function () {
        // prefix: /sports/about-us/admin-structure
        Route::prefix('admin-structure')->group(function () {
            Route::put('sort', [SpOrgStructureController::class, 'sort']);
            Route::apiResource('', SpOrgStructureController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::get('all', [SpOrgStructureController::class, 'all']);
            Route::put('toggle/{id}', [SpOrgStructureController::class, 'toggle']);
        });

        // prefix: /sports/about-us/key-personnel
        Route::prefix('key-personnel')->group(function () {
            Route::get('all', [KeyPersonnelController::class, 'all']);
            Route::put('sort', [KeyPersonnelController::class, 'sort']);
            Route::apiResource('', KeyPersonnelController::class)
                ->parameters(['' => 'id'])
                ->except(['show', 'update']);
            Route::post('update-member/{id}', [KeyPersonnelController::class, 'updateMember']);
            Route::put('toggle/{id}', [KeyPersonnelController::class, 'toggle']);
        });

        // prefix: /sports/about-us/achievements
        Route::prefix('achievements')->group(function () {
            Route::put('toggle/{id}', [AchievementController::class, 'toggle']);
            Route::apiResource('', AchievementController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });

    Route::prefix('sports')->group(function () {
        // prefix: /sports/sports/sports-personnel
        Route::prefix('sports-personnel')->group(function () {
            Route::put('toggle/{id}', [SportsPersonnelController::class, 'toggle']);
            Route::apiResource('/', SportsPersonnelController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });

        // prefix: /sports/sports/sports-events
        Route::prefix('sports-events')->group(function () {
            Route::put('toggle/{id}', [SportsEventController::class, 'toggle']);
            Route::apiResource('/', SportsEventController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });

    Route::prefix('wbs-council')->group(function () {
        // prefix: /sports/wbs-council/designations
        Route::get('designations', [WbsCouncilDesignationController::class, 'index']);

        // prefix: /sports/wbs-council/wbs-members
        Route::prefix('wbs-members')->group(function () {
            Route::put('toggle/{id}', [AdvisoryCommitteeController::class, 'toggle']);
            Route::apiResource('', AdvisoryCommitteeController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });

    Route::prefix('announcements')->group(function () {
        // prefix: /sports/announcements/announcements
        Route::prefix('announcements')->group(function () {
            Route::put('toggle/{id}', [AnnouncementsController::class, 'toggle']);
            Route::apiResource('', AnnouncementsController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });

        // prefix: /sports/announcements/advertisements
        Route::prefix('advertisements')->group(function () {
            Route::put('toggle/{id}', [AdvertisementController::class, 'toggle']);
            Route::apiResource('', AdvertisementController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });

    Route::prefix('achievements-awards')->group(function () {
        // prefix: /sports/achievements-awards/players-achievements
        Route::prefix('players-achievements')->group(function () {
            Route::put('toggle/{id}', [PlayersAchievementController::class, 'toggle']);
            Route::apiResource('', PlayersAchievementController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });

        // prefix: /sports/achievements-awards/awards
        Route::prefix('awards')->group(function () {
            Route::put('toggle/{id}', [AwardsController::class, 'toggle']);
            Route::apiResource('', AwardsController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });

    Route::prefix('info-about')->group(function () {
        // prefix: /sports/info-about/stadiums
        Route::prefix('stadiums')->group(function () {
            Route::put('toggle/{id}', [StadiumController::class, 'toggle']);
            Route::apiResource('', StadiumController::class)
                ->parameters(['' => 'id']);
        });

        // prefix: /sports/info-about/associations
        Route::prefix('associations')->group(function () {
            Route::put('toggle/{id}', [AssociationController::class, 'toggle']);
            Route::apiResource('', AssociationController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });

    Route::controller(PhotoGalleryController::class)->prefix('photo-galleries')->group(function () {
        Route::get('/{category}', 'index');
        Route::post('/', 'store');
        Route::post('/update/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::put('activate/{id}', 'activate');
        Route::get('/single/{id}', 'single');
        Route::post('/images/{id}', 'storeImages');
        Route::delete('/images/{id}', 'deleteImage');
    });

    Route::apiResource('amphan-photos', AmphanPhotoController::class)->except(['show', 'update']);
    Route::post('amphan-photos/update/{id}', [AmphanPhotoController::class, 'update']);

    Route::apiResource('audio-visuals', AudioVisualController::class)->except(['show']);

    Route::apiResource('bulletins', BulletinController::class)->except(['show', 'update']);
    Route::controller(BulletinController::class)->prefix('bulletins')->group(function () {
        Route::post('update/{id}', 'update');
        Route::put('activate/{id}', 'activate');
    });

    Route::apiResource('fifa', FifaController::class)->except(['show']);
    Route::controller(FifaController::class)->prefix('fifa')->group(function () {
        Route::put('activate/{id}', 'activate');
        Route::get('single/{id}', 'single');
        Route::post('images/{id}', 'storeImages');
        Route::delete('/images/{id}', 'deleteImage');
    });

    Route::apiResource('sports-policies', SportPolicyController::class)->except(['show', 'update']);
    Route::controller(SportPolicyController::class)->prefix('sports-policies')->group(function () {
        Route::post('update/{id}', 'update');
        Route::put('activate/{id}', 'activate');
    });

    Route::apiResource('assoc-sites', AssocSiteController::class)->except(['show']);
    Route::put('assoc-sites/activate/{id}', [AssocSiteController::class, 'activate']);

    Route::apiResource('rti-notices', RtiNoticeController::class)->except(['show', 'update']);
    Route::controller(RtiNoticeController::class)->prefix('rti-notices')->group(function () {
        Route::post('update/{id}', 'update');
        Route::put('activate/{id}', 'activate');
    });

    Route::put('contacts/set-order', [ContactController::class, 'contactsSetOrder']);
    Route::apiResource('contacts', ContactController::class)->except(['show']);
    Route::put('contacts/activate/{id}', [ContactController::class, 'activate']);

    Route::apiResource('news-scroll', NewsScrollController::class)->except(['show', 'update']);
    Route::post('news-scroll/update/{id}', [NewsScrollController::class, 'update']);
    Route::post('news-scroll/activate/{id}', [NewsScrollController::class, 'activate']);
});
Route::post('sports/send-feedback', [FeedbackController::class, 'sendFeedback']);
// Sports app routes end -------------------------------

// Sports website routes start -------------------------------

Route::controller(SportsWebsiteController::class)->prefix('sports')->group(function () {
    Route::get('homepage-slider/all', 'getHomepageSlider');
    Route::get('web-key-personnel/all', 'getKeyPersonnel');
    Route::get('sports-personnel/all', 'getSportsPersonnel');
    Route::get('achievements/all', 'getAchievementsAll');
    Route::get('org-structure/all', 'getOrgStructureAll');
    Route::get('wbs-designations/{type}', 'getWbsDesignations');
    Route::get('advisory-board', 'getAdvisoryBoard');
    Route::get('working-committee', 'getWorkingCommittee');
    Route::get('single-achievement/{slug}', 'getPlayersAchievementSingle');
    Route::get('events/all', 'getSportsEventsAll');
    Route::get('announcements/all/{type}', 'getAnnouncementsAll');
    Route::get('announcements/ltd/{type}/{count}', 'getAnnouncementsLtd');
    Route::get('awards/all', 'getAwardsAll');
    Route::get('stadiums/all', 'getStadiumsAll');
    Route::get('stadium-info/{slug}', 'getStadiumInfo');
    Route::get('images-landing/{count}', 'imagesLanding');
    Route::get('photo-galleries', 'galleryAll');
    Route::get('photo-gallery/{slug}', 'gallerySingle');
    Route::get('audio-visuals/all', 'audioVisualsAll');
    Route::get('amphan/all', 'getAmphanPhotos');
    Route::get('bulletins/all', 'getBulletinsAll');
    Route::get('advertisements/all', 'getAdvertisementsAll');
    Route::get('associations/all', 'getAssociationsAll');
    Route::get('fifa/all', 'fifaAll');
    Route::get('fifa/gallery/{slug}', 'fifaSingle');
    Route::get('sports-policies/all', 'sportsPoliciesAll');
    Route::get('assoc-sites/all', 'assocSitesAll');
    Route::get('rti-notices/all', 'getRtiNotices');
    Route::get('contacts/all', 'getContactsAll');
    Route::get('gallery-images-ltd/{count}', 'galleryImagesLtd');
    Route::get('news-events/scroll', 'newsScroller');
});
// Sports app routes end -------------------------------

Route::get('download', [DownloadController::class, 'download']);

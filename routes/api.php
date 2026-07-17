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
use App\Http\Controllers\Api\MountainCourseController;
use App\Http\Controllers\Api\MountainGeneralBodyController;
use App\Http\Controllers\Api\NewsEventsController;
use App\Http\Controllers\Api\OrganisationChartController;
use App\Http\Controllers\Api\ServicesHomepageScrollerController;
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
use App\Http\Controllers\Api\VocationalCentreController;
use App\Http\Controllers\Api\VocationalSchemeController;
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
Route::middleware(['cookie.auth:services', 'auth:api'])->prefix('services')->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        // prefix: /services/auth
        Route::post('logout/{organisation}', 'logout');
        Route::get('me', 'me');
        Route::post('update', 'profileUpdate');
    });

    Route::prefix('banners')->group(function () {
        // prefix: /services/banners/banners
        Route::prefix('banners')->group(function () {
            Route::apiResource('', BannerController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::put('toggle/{id}', [BannerController::class, 'toggle']);
        });
    });

    Route::prefix('about-us')->group(function () {
        // prefix: /services/about-us/org-chart
        Route::prefix('org-chart')->group(function () {
            Route::put('sort', [OrganisationChartController::class, 'sort']);
            Route::apiResource('', OrganisationChartController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::get('all', [OrganisationChartController::class, 'all']);
            Route::put('toggle/{id}', [OrganisationChartController::class, 'toggle']);
        });

        // prefix: /services/about-us/district-block-offices
        Route::prefix('district-block-offices')->group(function () {
            Route::apiResource('', DistrictBlockOfficeController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::put('toggle/{id}', [DistrictBlockOfficeController::class, 'toggle']);
        });
    });

    Route::prefix('computer-training')->group(function () {
        // prefix: /services/computer-training/course-details
        Route::prefix('course-details')->group(function () {
            Route::apiResource('', ComputerTraining::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::put('toggle/{id}', [ComputerTraining::class, 'toggle']);
        });

        // prefix: /services/computer-training/course-syllabus
        Route::prefix('course-syllabus')->group(function () {
            Route::apiResource('', CompSyllabusController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::put('toggle/{id}', [CompSyllabusController::class, 'toggle']);
        });

        // prefix: /services/computer-training/training-centres
        Route::prefix('training-centres')->group(function () {
            Route::apiResource('', CompCentreController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::put('toggle/{id}', [CompCentreController::class, 'toggle']);
        });
    });

    Route::prefix('vocatioanl-training')->group(function () {
        // prefix: /services/vocatioanl-training/schemes
        Route::prefix('schemes')->group(function () {
            Route::put('sort', [VocationalSchemeController::class, 'sort']);
            Route::apiResource('', VocationalSchemeController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::get('all', [VocationalSchemeController::class, 'all']);
            Route::put('toggle/{id}', [VocationalSchemeController::class, 'toggle']);
        });

        // prefix: /services/vocatioanl-training/training-centres
        Route::prefix('training-centres')->group(function () {
            Route::apiResource('', VocationalCentreController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::put('toggle/{id}', [VocationalCentreController::class, 'toggle']);
        });
    });

    Route::prefix('mountaineering')->group(function () {
        // prefix: /services/mountaineering/general-body
        Route::prefix('general-body')->group(function () {
            Route::put('sort', [MountainGeneralBodyController::class, 'sort']);
            Route::apiResource('', MountainGeneralBodyController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::get('all', [MountainGeneralBodyController::class, 'all']);
            Route::put('toggle/{id}', [MountainGeneralBodyController::class, 'toggle']);
        });

        // prefix: /services/mountaineering/course-details
        Route::prefix('course-details')->group(function () {
            Route::apiResource('', MountainCourseController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
            Route::put('toggle/{id}', [MountainCourseController::class, 'toggle']);
        });
    });

    // prefix: /services/fair-programmes/fair-programmes
    // same routes are used for photo-gallery as well
    Route::prefix('fair-programmes/fair-programmes')->group(function () {
        Route::put('toggle/{id}', [FairProgrammeController::class, 'toggle']);
        Route::post('photos/{id}', [FairProgrammeController::class, 'upload']);
        Route::apiResource('', FairProgrammeController::class)
            ->parameters(['' => 'id']);
    });

    // prefix: /services/news-events/news-events
    Route::prefix('news-events/news-events')->group(function () {
        Route::put('toggle/{id}', [NewsEventsController::class, 'toggle']);
        Route::apiResource('', NewsEventsController::class)
            ->parameters(['' => 'id']);
    });

    // prefix: /services/youth-hostels/youth-hostels
    Route::prefix('youth-hostels/youth-hostels')->group(function () {
        Route::put('toggle/{id}', [YouthHostelController::class, 'toggle']);
        Route::apiResource('', YouthHostelController::class)
            ->parameters(['' => 'id']);
    });

    // prefix: /services/e-tenders/e-tenders
    Route::prefix('e-tenders/e-tenders')->group(function () {
        Route::put('toggle/{id}', [ServiceTenderController::class, 'toggle']);
        Route::apiResource('', ServiceTenderController::class)
            ->parameters(['' => 'id']);
    });

    // prefix: /services/homepage-scroller/homepage-scroller
    Route::prefix('homepage-scroller/homepage-scroller')->group(function () {
        Route::put('toggle/{id}', [ServicesHomepageScrollerController::class, 'toggle']);
        Route::apiResource('', ServicesHomepageScrollerController::class)
            ->parameters(['' => 'id'])
            ->except(['show']);
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
Route::middleware(['cookie.auth:sports', 'auth:api'])->prefix('sports')->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        // prefix: /sports/auth
        Route::post('logout/{organisation}', 'logout');
        Route::get('me', 'me');
        Route::post('update', 'profileUpdate');
    });

    Route::prefix('homepage-sliders')->group(function () {
        // prefix: /sports/homepage-sliders/homepage-sliders
        Route::prefix('homepage-sliders')->group(function () {
            Route::apiResource('', HomepageSliderController::class)
                ->parameters(['' => 'id'])
                ->except(['show', 'update']);
            Route::put('toggle/{id}', [HomepageSliderController::class, 'toggle']);
        });
    });

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

        // prefix: /sports/info-about/fifa
        Route::prefix('fifa')->group(function () {
            Route::put('toggle/{id}', [FifaController::class, 'toggle']);
            Route::post('photos/{id}', [FifaController::class, 'upload']);
            Route::apiResource('', FifaController::class)
                ->parameters(['' => 'id']);
        });

        // prefix: /sports/info-about/sports-policies
        Route::prefix('sports-policies')->group(function () {
            Route::put('toggle/{id}', [SportPolicyController::class, 'toggle']);
            Route::apiResource('', SportPolicyController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });

        // prefix: /sports/info-about/assoc-sites
        Route::prefix('assoc-sites')->group(function () {
            Route::put('toggle/{id}', [AssocSiteController::class, 'toggle']);
            Route::apiResource('', AssocSiteController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });

    Route::prefix('moments')->group(function () {
        // prefix: /sports/moments/photo-galleries
        Route::prefix('photo-galleries')->group(function () {
            Route::put('toggle/{id}', [PhotoGalleryController::class, 'toggle']);
            Route::post('photos/{id}', [PhotoGalleryController::class, 'upload']);
            Route::apiResource('', PhotoGalleryController::class)
                ->parameters(['' => 'id']);
        });

        // prefix: /sports/moments/audio-visuals
        Route::prefix('audio-visuals')->group(function () {
            Route::apiResource('', AudioVisualController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });

        // prefix: /sports/moments/bulletins
        Route::prefix('bulletins')->group(function () {
            Route::put('toggle/{id}', [BulletinController::class, 'toggle']);
            Route::apiResource('', BulletinController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });

        // prefix: /sports/moments/amphan-photos
        Route::prefix('amphan-photos')->group(function () {
            Route::apiResource('', AmphanPhotoController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });

    Route::prefix('rti')->group(function () {
        // prefix: /sports/rti/notices
        Route::prefix('notices')->group(function () {
            Route::put('toggle/{id}', [RtiNoticeController::class, 'toggle']);
            Route::apiResource('', RtiNoticeController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });

    Route::prefix('contact-us')->group(function () {
        // prefix: /sports/contact-us/contact-us
        Route::prefix('contact-us')->group(function () {
            Route::put('toggle/{id}', [ContactController::class, 'toggle']);
            Route::get('all', [ContactController::class, 'all']);
            Route::put('sort', [ContactController::class, 'sort']);
            Route::apiResource('', ContactController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });

    Route::prefix('news-scroll')->group(function () {
        // prefix: /sports/news-scroll/news-scroll
        Route::prefix('news-scroll')->group(function () {
            Route::put('toggle/{id}', [NewsScrollController::class, 'toggle']);
            Route::apiResource('', NewsScrollController::class)
                ->parameters(['' => 'id'])
                ->except(['show']);
        });
    });
});
Route::post('sports/send-feedback', [FeedbackController::class, 'sendFeedback']);
// Sports app routes end -------------------------------

// Sports website routes start -------------------------------

Route::controller(SportsWebsiteController::class)->prefix('sports/website')->group(function () {
    Route::get('homepage-slider/all', 'getHomepageSlider');
    Route::get('news-events/scroll', 'newsScroller');
    Route::get('org-structure/all', 'getOrgStructureAll');
    Route::get('web-key-personnel/all', 'getKeyPersonnel');
    Route::get('achievements/all', 'getAchievementsAll');
    Route::get('sports-personnel/all', 'getSportsPersonnel');
    Route::get('sports-personnel-web/{sport}', 'sportwiseSportsPersonnel');
    Route::get('events/all', 'getSportsEventsAll');
    Route::get('gallery-images-ltd/{count}', 'galleryImagesLtd');
    Route::get('wbs-designations/{type}', 'getWbsDesignations');
    Route::get('advisory-board', 'getAdvisoryBoard');
    Route::get('working-committee', 'getWorkingCommittee');
    Route::get('announcements/all/{type}', 'getAnnouncementsAll');
    Route::get('announcements/ltd/{type}/{count}', 'getAnnouncementsLtd');
    Route::get('advertisements/all', 'getAdvertisementsAll');
    Route::get('single-achievement/{slug}', 'getPlayersAchievementSingle');
    Route::get('awards/all', 'getAwardsAll');
    Route::get('stadiums/all', 'getStadiumsAll');
    Route::get('stadium-info/{slug}', 'getStadiumInfo');
    Route::get('associations/all', 'getAssociationsAll');
    Route::get('fifa/all', 'fifaAll');
    Route::get('fifa/gallery/{slug}', 'fifaSingle');
    Route::get('sports-policies/all', 'sportsPoliciesAll');
    Route::get('assoc-sites/all', 'assocSitesAll');
    Route::get('photo-galleries', 'galleryAll');
    Route::get('photo-gallery/{slug}', 'gallerySingle');
    Route::get('audio-visuals/all', 'audioVisualsAll');
    Route::get('bulletins/all', 'getBulletinsAll');
    Route::get('amphan/all', 'getAmphanPhotos');
    Route::get('rti-notices/all', 'getRtiNotices');
    Route::get('images-landing/{count}', 'imagesLanding');
    Route::get('contacts/all', 'getContactsAll');
});
// Sports app routes end -------------------------------

Route::get('preview', [DownloadController::class, 'preview']);
Route::get('download', [DownloadController::class, 'download']);

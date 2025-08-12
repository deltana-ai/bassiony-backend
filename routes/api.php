<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ContactPeopleController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExpoCompanyController;
use App\Http\Controllers\ExpoController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\GuideLineController;
use App\Http\Controllers\LogoCompanyController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PageSectionController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\RefController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TermsConditionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [UserController::class, 'login']);
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::post('application-form', [UserController::class, 'applicationForm']);
Route::post('/log/index', [UserController::class, 'logIndex']);
Route::get('/user-total-count-country', [UserController::class, 'totalCountPerCountry']);

//////////////////////////////////////// user ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/user/index', [UserController::class, 'index']);
    Route::post('user/restore', [UserController::class, 'restore']);
    Route::delete('user/delete', [UserController::class, 'destroy']);
    Route::put('/user/{id}/{column}', [UserController::class, 'toggle']);
    Route::delete('user/force-delete', [UserController::class, 'forceDelete']);
    Route::apiResource('user', UserController::class);
});


Route::get('/get-user-public', [UserController::class, 'indexPublic']);
Route::get('/get-user-active', [UserController::class, 'indexActive']);

//////////////////////////////////////// user ////////////////////////////////

////////////////////////////////////////// Admin ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/admin/index', [AdminController::class, 'index']);
    Route::post('admin/restore', [AdminController::class, 'restore']);
    Route::delete('admin/delete', [AdminController::class, 'destroy']);
    Route::delete('admin/force-delete', [AdminController::class, 'forceDelete']);
    Route::put('/admin/{id}/{column}', [AdminController::class, 'toggle']);
    Route::post('/admin-select', [AdminController::class, 'index']);
    Route::post('/admin-logout', [AdminController::class, 'logout']);
    Route::get('/get-admin', [AdminController::class, 'getCurrentAdmin']);
    Route::apiResource('admin', AdminController::class);
});
Route::post('/admin/login', [AdminController::class, 'login']);
////////////////////////////////////////// Admin ////////////////////////////////

////////////////////////////////////////// page ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/page/index', [PageController::class, 'index']);
    Route::post('page/restore', [PageController::class, 'restore']);
    Route::delete('page/delete', [PageController::class, 'destroy']);
    Route::delete('page/force-delete', [PageController::class, 'forceDelete']);
    Route::put('/page/{id}/{column}', [PageController::class, 'toggle']);
    Route::apiResource('page', PageController::class);
});

Route::get('/get-page/{slug}', [PageController::class, 'showPublic']);

////////////////////////////////////////// page ////////////////////////////////

/////////////////////////////////// PageSection ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('page-section/index', [PageSectionController::class, 'index']);
    Route::post('page-section/restore', [PageSectionController::class, 'restore']);
    Route::delete('page-section/delete', [PageSectionController::class, 'destroy']);
    Route::delete('page-section/force-delete', [PageSectionController::class, 'forceDelete']);
    Route::put('page-section/{id}/{column}', [PageSectionController::class, 'toggle']);
    Route::apiResource('page-section', PageSectionController::class);
});
//////////////////////////////////// PageSection ////////////////////////////////

//////////////////////////////////////// Setting ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/setting/index', [SettingController::class, 'index']);
    Route::put('/setting/{id}/{column}', [SettingController::class, 'toggle']);
    Route::post('setting/restore', [SettingController::class, 'restore']);
    Route::delete('setting/delete', [SettingController::class, 'destroy']);
    Route::delete('setting/force-delete', [SettingController::class, 'forceDelete']);
    Route::apiResource('setting', SettingController::class);
    Route::get('/setting/show-section/{setting}', [SettingController::class, 'showSectionItems']);
    Route::get('setting-section', [SettingController::class, 'settingSections']);
    Route::post('/setting/section-update', [SettingController::class, 'updateSettings']);
});
Route::get('/setting-public', [SettingController::class, 'publicIndexSetting']);
///////////////////////////////////////// Setting ////////////////////////////////

//////////////////////////////////////// Menu ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/menu/index', [MenuController::class, 'index']);
    Route::post('menu/restore', [MenuController::class, 'restore']);
    Route::delete('menu/delete', [MenuController::class, 'destroy']);
    Route::delete('menu/force-delete', [MenuController::class, 'forceDelete']);
    Route::put('/menu/{id}/{column}', [MenuController::class, 'toggle']);
    Route::post('/menu-select', [MenuController::class, 'index']);
    Route::apiResource('menu', MenuController::class);
});
Route::get('/get-menu/{menu}', [MenuController::class, 'showPublic']);

//////////////////////////////////////// Menu ////////////////////////////////

//////////////////////////////////////// Menu Item ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/menu-item/index', [MenuItemController::class, 'index']);
    Route::post('menu-item/restore', [MenuItemController::class, 'restore']);
    Route::delete('menu-item/delete/{id}', [MenuItemController::class, 'delete']);
    Route::delete('menu-item/force-delete', [MenuItemController::class, 'forceDelete']);
    Route::put('menu-item/{id}/{column}', [MenuItemController::class, 'toggle']);
    Route::post('menu-item-select', [MenuItemController::class, 'index']);
    Route::apiResource('menu-item', MenuItemController::class);
});
//////////////////////////////////////// Menu Item ////////////////////////////////


Route::group(['middleware' => ['api']], static function () {
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/{media}', [MediaController::class, 'show']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
    Route::get('/get-unused-media', [MediaController::class, 'getUnUsedImages']);
    Route::delete('/delete-unused-media', [MediaController::class, 'deleteUnUsedImages']);
});
Route::get('/get-media/{media}', [MediaController::class, 'show']);
Route::post('/media-array', [MediaController::class, 'showMedia']);
Route::post('/media-upload-many', [MediaController::class, 'storeMany']);

//////////////////////////////////////// Menu Item ////////////////////////////////

//////////////////////////////////////// Admin ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/role/index', [RoleController::class, 'index']);
    Route::post('role/restore', [RoleController::class, 'restore']);
    Route::delete('role/delete', [RoleController::class, 'destroy']);
    Route::delete('role/force-delete', [RoleController::class, 'forceDelete']);
    Route::apiResource('role', RoleController::class);
    Route::post('/permission/index', [PermissionController::class, 'index']);
    Route::apiResource('permission', PermissionController::class);
});
Route::get('admin-permissions/{id}', [RoleController::class, 'adminPermissions']);

//////////////////////////////////////// Admin ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/role/index', [RoleController::class, 'index']);
    Route::post('role/restore', [RoleController::class, 'restore']);
    Route::delete('role/delete', [RoleController::class, 'destroy']);
    Route::delete('role/force-delete', [RoleController::class, 'forceDelete']);
    Route::apiResource('role', RoleController::class);
    Route::post('/permission/index', [PermissionController::class, 'index']);
    Route::apiResource('permission', PermissionController::class);
});
Route::get('admin-permissions/{id}', [RoleController::class, 'adminPermissions']);


//////////////////////////////////////// Country ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/country/index', [CountryController::class, 'index']);
    Route::post('country/restore', [CountryController::class, 'restore']);
    Route::delete('country/delete', [CountryController::class, 'destroy']);
    Route::delete('country/force-delete', [CountryController::class, 'forceDelete']);
    Route::put('/country/{id}/{column}', [CountryController::class, 'toggle']);
    Route::apiResource('country', CountryController::class);
});
Route::get('/fetch-resources', [CountryController::class, 'indexAll']);

//////////////////////////////////////// Country ////////////////////////////////

//////////////////////////////////////// City ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/city/index', [CityController::class, 'index']);
    Route::post('city/restore', [CityController::class, 'restore']);
    Route::delete('city/delete', [CityController::class, 'destroy']);
    Route::delete('city/force-delete', [CityController::class, 'forceDelete']);
    Route::put('/city/{id}/{column}', [CityController::class, 'toggle']);
    Route::apiResource('city', CityController::class);
});

//////////////////////////////////////// City ////////////////////////////////


//////////////////////////////////////// faq ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/faq/index', [FAQController::class, 'index']);
    Route::post('faq/restore', [FAQController::class, 'restore']);
    Route::delete('faq/delete', [FAQController::class, 'destroy']);
    Route::delete('faq/force-delete', [FAQController::class, 'forceDelete']);
    Route::put('/faq/{id}/{column}', [FAQController::class, 'toggle']);
    Route::apiResource('faq', FAQController::class);
});

//////////////////////////////////////// faq ////////////////////////////////



//////////////////////////////////////// article ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/article/index', [ArticleController::class, 'index']);
    Route::post('article/restore', [ArticleController::class, 'restore']);
    Route::delete('article/delete', [ArticleController::class, 'destroy']);
    Route::delete('article/force-delete', [ArticleController::class, 'forceDelete']);
    Route::put('/article/{id}/{column}', [ArticleController::class, 'toggle']);
    Route::post('/article-select', [ArticleController::class, 'index']);
    Route::apiResource('article', ArticleController::class);
});
Route::get('get-article/{slug}', [ArticleController::class, 'showPublic']);
Route::get('/get-article', [ArticleController::class, 'indexPublic']);
Route::get('/get-article-last', [ArticleController::class, 'indexPublicltes']);


//////////////////////////////////////// article ////////////////////////////////


//////////////////////////////////////// event ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/event/index', [EventController::class, 'index']);
    Route::post('event/restore', [EventController::class, 'restore']);
    Route::delete('event/delete', [EventController::class, 'destroy']);
    Route::delete('event/force-delete', [EventController::class, 'forceDelete']);
    Route::put('/event/{id}/{column}', [EventController::class, 'toggle']);
    Route::post('/event-select', [EventController::class, 'index']);
    Route::apiResource('event', EventController::class);
});
Route::get('get-event/{slug}', [EventController::class, 'showPublic']);
Route::get('/get-event', [EventController::class, 'indexPublic']);

//////////////////////////////////////// event ////////////////////////////////


//////////////////////////////////////// Service ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/service/index', [ServiceController::class, 'index']);
    Route::post('service/restore', [ServiceController::class, 'restore']);
    Route::delete('service/delete', [ServiceController::class, 'destroy']);
    Route::delete('service/force-delete', [ServiceController::class, 'forceDelete']);
    Route::put('/service/{id}/{column}', [ServiceController::class, 'toggle']);
    Route::apiResource('service', ServiceController::class);
});
Route::get('get-service/{slug}', [ServiceController::class, 'showPublic']);

//////////////////////////////////////// Service ////////////////////////////////


//////////////////////////////////////// ContactUs ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/contactus/index', [ContactUsController::class, 'index']);
    Route::post('contactus/restore', [ContactUsController::class, 'restore']);
    Route::delete('contactus/delete', [ContactUsController::class, 'destroy']);
    Route::delete('contactus/force-delete', [ContactUsController::class, 'forceDelete']);
    Route::put('/contactus/{id}/{column}', [ContactUsController::class, 'toggle']);
    Route::apiResource('contactus', ContactUsController::class);
});
Route::post('contact-us-public', [ContactUsController::class, 'store']);
Route::post('publicsss', [ContactUsController::class, 'aaaa']);

//////////////////////////////////////// ContactUs ////////////////////////////////


//////////////////////////////////////// email-template ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/email-template/index', [EmailTemplateController::class, 'index']);
    Route::post('email-template/restore', [EmailTemplateController::class, 'restore']);
    Route::delete('email-template/delete', [EmailTemplateController::class, 'destroy']);
    Route::put('/email-template/{id}/{column}', [EmailTemplateController::class, 'toggle']);
    Route::delete('email-template/force-delete', [EmailTemplateController::class, 'forceDelete']);
    Route::apiResource('email-template', EmailTemplateController::class);
});

//////////////////////////////////////// email-template ////////////////////////////////


//////////////////////////////////////// policy ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/policy/index', [PolicyController::class, 'index']);
    Route::post('policy/restore', [PolicyController::class, 'restore']);
    Route::delete('policy/delete', [PolicyController::class, 'destroy']);
    Route::put('/policy/{id}/{column}', [PolicyController::class, 'toggle']);
    Route::delete('policy/force-delete', [PolicyController::class, 'forceDelete']);
    Route::apiResource('policy', PolicyController::class);
});
Route::get('/get-policy', [PolicyController::class, 'indexPublic']);
Route::get('get-policy/{slug}', [PolicyController::class, 'showPublic']);

//////////////////////////////////////// policy ////////////////////////////////


//////////////////////////////////////// contact_people ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/contact-people/index', [ContactPeopleController::class, 'index']);
    Route::post('contact-people/restore', [ContactPeopleController::class, 'restore']);
    Route::delete('contact-people/delete', [ContactPeopleController::class, 'destroy']);
    Route::put('/contact-people/{id}/{column}', [ContactPeopleController::class, 'toggle']);
    Route::delete('contact-people/force-delete', [ContactPeopleController::class, 'forceDelete']);
    Route::apiResource('contact-people', ContactPeopleController::class);
});

//////////////////////////////////////// contact_people ////////////////////////////////



//////////////////////////////////////// guideLine ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/guide-line/index', [GuideLineController::class, 'index']);
    Route::post('guide-line/restore', [GuideLineController::class, 'restore']);
    Route::delete('guide-line/delete', [GuideLineController::class, 'destroy']);
    Route::put('/guide-line/{id}/{column}', [GuideLineController::class, 'toggle']);
    Route::delete('guide-line/force-delete', [GuideLineController::class, 'forceDelete']);
    Route::apiResource('guide-line', GuideLineController::class);
});
Route::get('/get-guide-line', [GuideLineController::class, 'indexPublic']);
Route::get('get-guide-line/{slug}', [GuideLineController::class, 'showPublic']);

//////////////////////////////////////// guideLine ////////////////////////////////

//////////////////////////////////////// termsCondition ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/terms-condition/index', [TermsConditionController::class, 'index']);
    Route::post('terms-condition/restore', [TermsConditionController::class, 'restore']);
    Route::delete('terms-condition/delete', [TermsConditionController::class, 'destroy']);
    Route::put('/terms-condition/{id}/{column}', [TermsConditionController::class, 'toggle']);
    Route::delete('terms-condition/force-delete', [TermsConditionController::class, 'forceDelete']);
    Route::apiResource('terms-condition', TermsConditionController::class);
});

//////////////////////////////////////// termsCondition ////////////////////////////////


//////////////////////////////////////// partner ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/partner/index', [PartnerController::class, 'index']);
    Route::post('partner/restore', [PartnerController::class, 'restore']);
    Route::delete('partner/delete', [PartnerController::class, 'destroy']);
    Route::put('/partner/{id}/{column}', [PartnerController::class, 'toggle']);
    Route::delete('partner/force-delete', [PartnerController::class, 'forceDelete']);
    Route::apiResource('partner', PartnerController::class);
});

//////////////////////////////////////// partner ////////////////////////////////



//////////////////////////////////////// visit ////////////////////////////////
Route::middleware(['admin'])->group(function () {
    Route::post('/visit/index', [VisitController::class, 'index']);
    Route::get('/visit/index/ip', [VisitController::class, 'indexIp']);
    Route::delete('visit/delete', [VisitController::class, 'destroy']);
    Route::put('/visit/{id}/{column}', [VisitController::class, 'toggle']);
    Route::apiResource('visit', VisitController::class);
});


Route::post('visit/guest/store', [VisitController::class, 'storeGuest']);
Route::post('visit/auth/store', [VisitController::class, 'storeAuth'])->middleware('auth:sanctum');
//////////////////////////////////////// visit ////////////////////////////////





//////////////////////////////////////// Slider ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('slider/index', [SliderController::class, 'index']);
    Route::post('slider/restore', [SliderController::class, 'restore']);
    Route::delete('slider/delete', [SliderController::class, 'destroy']);
    Route::put('/slider/{id}/{column}', [SliderController::class, 'toggle']);
    Route::delete('slider/force-delete', [SliderController::class, 'forceDelete']);
    Route::apiResource('slider', SliderController::class);
});

Route::get('/get-slider', [SliderController::class, 'indexPublic']);

//////////////////////////////////////// Slider ////////////////////////////////


//////////////////////////////////////// ref ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/ref/index', [RefController::class, 'index']);
    Route::post('ref/restore', [RefController::class, 'restore']);
    Route::delete('ref/delete', [RefController::class, 'destroy']);
    Route::put('/ref/{id}/{column}', [RefController::class, 'toggle']);
    Route::delete('ref/force-delete', [RefController::class, 'forceDelete']);
    Route::apiResource('ref', RefController::class);
});

Route::post('/ref-select', [RefController::class, 'index']);

//////////////////////////////////////// ref ////////////////////////////////



//////////////////////////////////////// team ////////////////////////////////

Route::middleware([])->group(function () {
    Route::post('/team/index', [TeamController::class, 'index']);
    Route::post('team/restore', [TeamController::class, 'restore']);
    Route::delete('team/delete', [TeamController::class, 'destroy']);
    Route::delete('team/force-delete', [TeamController::class, 'forceDelete']);
    Route::put('/team/{id}/{column}', [TeamController::class, 'toggle']);
    Route::apiResource('team', TeamController::class);
});

Route::post('/team-select', [TeamController::class, 'index']);
Route::get('/get-team', [TeamController::class, 'indexPublic']);

//////////////////////////////////////// logo company ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/logo-company/index', [LogoCompanyController::class, 'index']);
    Route::post('logo-company/restore', [LogoCompanyController::class, 'restore']);
    Route::delete('logo-company/delete', [LogoCompanyController::class, 'destroy']);
    Route::delete('logo-company/force-delete', [LogoCompanyController::class, 'forceDelete']);
    Route::put('/logo-company/{id}/{column}', [LogoCompanyController::class, 'toggle']);
    Route::apiResource('logo-company', LogoCompanyController::class);
});

Route::get('/get-logo-company/public', [LogoCompanyController::class, 'indexPublic']);
Route::get('/get-expo-list', [LogoCompanyController::class, 'indexPublicExpo']);


//////////////////////////////////////// package ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/package/index', [PackageController::class, 'index']);
    Route::post('package/restore', [PackageController::class, 'restore']);
    Route::delete('package/delete', [PackageController::class, 'destroy']);
    Route::delete('package/force-delete', [PackageController::class, 'forceDelete']);
    Route::put('/package/{id}/{column}', [PackageController::class, 'toggle']);
    Route::apiResource('package', PackageController::class);
});

Route::get('/get-package/public', [PackageController::class, 'indexPublic']);


//////////////////////////////////////// Expo ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/expo/index', [ExpoController::class, 'index']);
    Route::post('expo/restore', [ExpoController::class, 'restore']);
    Route::delete('expo/delete', [ExpoController::class, 'destroy']);
    Route::delete('expo/force-delete', [ExpoController::class, 'forceDelete']);
    Route::put('/expo/{id}/{column}', [ExpoController::class, 'toggle']);
    Route::apiResource('expo', ExpoController::class);
});

Route::get('/get-expo/public', [ExpoController::class, 'indexPublic']);
Route::get('/get-current-expo', [ExpoController::class, 'showPublic']);


//////////////////////////////////////// Expo Company ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/expo-company/index', [ExpoCompanyController::class, 'index']);
    Route::post('expo-company/restore', [ExpoCompanyController::class, 'restore']);
    Route::delete('expo-company/delete', [ExpoCompanyController::class, 'destroy']);
    Route::delete('expo-company/force-delete', [ExpoCompanyController::class, 'forceDelete']);
    Route::put('/expo-company/{id}/{column}', [ExpoCompanyController::class, 'toggle']);
    Route::apiResource('expo-company', ExpoCompanyController::class);
});
Route::post('expo/application-form', [ExpoCompanyController::class, 'applicationForm']);

Route::get('/get-expo-company/public', [ExpoCompanyController::class, 'indexPublic']);




//////////////////////////////////////// newsletter ////////////////////////////////

Route::middleware(['admin'])->group(function () {
    Route::post('/newsletter/index', [NewsletterController::class, 'index']);
    Route::post('newsletter/restore', [NewsletterController::class, 'restore']);
    Route::delete('newsletter/delete', [NewsletterController::class, 'destroy']);
    Route::put('/newsletter/{id}/{column}', [NewsletterController::class, 'toggle']);
    Route::delete('newsletter/force-delete', [NewsletterController::class, 'forceDelete']);
    Route::apiResource('newsletter', NewsletterController::class);
});

Route::post('/create-newsletter', [NewsletterController::class, 'createNewsletter']);

//////////////////////////////////////// newsletter ////////////////////////////////




<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Generic Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Bienvenido');
})->name('default');


/*
|--------------------------------------------------------------------------
| Administrator routes
|--------------------------------------------------------------------------
*/

/* >>>>>Assessment Periods routes <<<<<< */
Route::inertia('/assessmentPeriods', 'AssessmentPeriods/Index')->middleware(['auth', 'isAdmin'])->name('assessmentPeriods.index.view');
Route::resource('api/assessmentPeriods', \App\Http\Controllers\AssessmentPeriodController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/assessmentPeriods/{assessmentPeriod}/setActive', [\App\Http\Controllers\AssessmentPeriodController::class, 'setActive'])->middleware(['auth', 'isAdmin'])->name('api.assessmentPeriods.setActive');

/* >>>>>Assessment Periods routes <<<<<< */
Route::inertia('/forms', 'Forms/Index')->middleware(['auth', 'isAdmin'])->name('forms.index.view');
Route::resource('api/forms', \App\Http\Controllers\FormController::class, [
    'as' => 'api'
])->middleware('auth');


/* >>>>>Academic Periods routes <<<<<< */
Route::inertia('/academicPeriods', 'AcademicPeriods/Index')->middleware(['auth', 'isAdmin'])->name('academicPeriods.index.view');
Route::resource('api/academicPeriods', \App\Http\Controllers\AcademicPeriodController::class, [
    'as' => 'api'
])->middleware('auth');
//Sync periods from SIGA
Route::post('/api/academicPeriods/sync', [\App\Http\Controllers\AcademicPeriodController::class, 'sync'])->middleware(['auth'])->name('api.academicPeriods.sync');


/* >>>>> Units routes <<<<<< */
Route::inertia('/units', 'Unities/Index')->middleware(['auth', 'isAdmin'])->name('unities.index.view');
Route::resource('api/units', \App\Http\Controllers\UnityController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/units/sync', [\App\Http\Controllers\UnityController::class, 'sync'])->middleware(['auth'])->name('api.units.sync');

/* >>>>> Service Areas routes <<<<<< */
Route::inertia('/serviceAreas', 'ServiceAreas/Index')->middleware(['auth', 'isAdmin'])->name('serviceAreas.index.view');
Route::resource('api/serviceAreas', \App\Http\Controllers\ServiceAreaController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/serviceAreas/sync', [\App\Http\Controllers\ServiceAreaController::class, 'sync'])->middleware(['auth'])->name('api.serviceAreas.sync');
Route::get('borrarForm/{form}', [\App\Http\Controllers\FormController::class, 'destroy']);

/* >>>>> Groups routes <<<<<< */
Route::inertia('/groups', 'Groups/Index')->middleware(['auth', 'isAdmin'])->name('groups.index.view');
//Create fake unites
Route::resource('api/groups', \App\Http\Controllers\GroupController::class, [
    'as' => 'api'
])->middleware('auth');
//Sync groups
Route::post('/api/groups/sync', [\App\Http\Controllers\GroupController::class, 'sync'])->middleware(['auth'])->name('api.groups.sync');


/* >>>>> Teacher routes <<<<<< */
Route::inertia('/teachers', 'Teachers/Index')->middleware(['auth', 'isAdmin'])->name('teachers.index.view');
//Create fake unites
//Change teacher status
Route::post('api/teachers/{teacher}/status', [\App\Http\Controllers\TeacherProfileController::class, 'changeStatus'])->middleware(['auth'])->name('api.teachers.changeStatus');
Route::resource('api/teachers', \App\Http\Controllers\TeacherProfileController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/teachers/sync', [\App\Http\Controllers\TeacherProfileController::class, 'sync'])->middleware(['auth'])->name('api.teachers.sync');


/* >>>>>Roles routes <<<<<< */
//Get all roles
Route::get('/roles', [\App\Http\Controllers\Roles\RoleController::class, 'index'])->middleware(['auth', 'isAdmin'])->name('roles.index');
Route::resource('api/roles', \App\Http\Controllers\Roles\ApiRoleController::class, [
    'as' => 'api'
])->middleware('auth');

/* >>>>>User routes <<<<<< */
//Get all users
Route::get('/users', [\App\Http\Controllers\Users\UserController::class, 'index'])->middleware(['auth', 'isAdmin'])->name('users.index');
//users api
Route::resource('api/users', \App\Http\Controllers\Users\ApiUserController::class, [
    'as' => 'api'
])->middleware('auth');
//Update user role
Route::patch('/api/users/{user}/roles', [\App\Http\Controllers\Users\ApiUserController::class, 'updateUserRoles'])->middleware('auth')->name('api.users.roles.update');
Route::get('/api/users/{user}/roles', [\App\Http\Controllers\Users\ApiUserController::class, 'getUserRoles'])->middleware('auth')->name('api.users.roles.show');
Route::post('/api/users/{user}/roles/select', [\App\Http\Controllers\Users\ApiUserController::class, 'selectRole'])->middleware('auth')->name('api.users.roles.selectRole');

/* >>>>>Roles routes <<<<<< */
Route::get('landing', function () {
    return Inertia::render('SuperTest');
})->name('landing')->middleware(['auth']);


//Auth routes
Route::get('login', [\App\Http\Controllers\AuthController::class, 'redirectGoogleLogin'])->name('login');
Route::get('/google/callback', [\App\Http\Controllers\AuthController::class, 'handleGoogleCallback']);
Route::get('/pickRole', [\App\Http\Controllers\AuthController::class, 'pickRole'])->name('pickRole');


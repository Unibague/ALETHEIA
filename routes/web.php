<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Administrator routes
|--------------------------------------------------------------------------
*/

/* >>>>>>>>>>>>>>>>>>>>>>>  Assessment Periods routes >>>>>>>><<<<<< */
Route::inertia('/assessmentPeriods', 'AssessmentPeriods/Index')->middleware(['auth', 'isAdmin'])->name('assessmentPeriods.index.view');
Route::resource('api/assessmentPeriods', \App\Http\Controllers\AssessmentPeriodController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/assessmentPeriods/{assessmentPeriod}/setActive', [\App\Http\Controllers\AssessmentPeriodController::class, 'setActive'])->middleware(['auth', 'isAdmin'])->name('api.assessmentPeriods.setActive');

/* >>>>>>>>>>>>>>>>>>>>> Forms routes <<<<<<<<<<<<<<<<<<<< */
Route::get('api/forms/withoutQuestions', [\App\Http\Controllers\FormController::class, 'getWithoutQuestions'])->name('api.forms.withoutQuestions')->middleware(['auth', 'isAdmin']);
Route::inertia('/forms', 'Forms/Index')->middleware(['auth', 'isAdmin'])->name('forms.index.view');
Route::inertia('/forms/{form}', 'Forms/Show')->middleware(['auth', 'isAdmin'])->name('forms.show.view');
Route::resource('api/forms', \App\Http\Controllers\FormController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('api/forms/{form}/copy', [\App\Http\Controllers\FormController::class, 'copy'])->name('api.forms.copy')->middleware(['auth']);
Route::patch('api/forms/{form}/formQuestions', [\App\Http\Controllers\FormQuestionController::class, 'storeOrUpdate'])->name('api.forms.questions.store')->middleware(['auth']);
Route::get('api/forms/{form}/formQuestions', [\App\Http\Controllers\FormQuestionController::class, 'getByFormId'])->name('api.forms.questions.show')->middleware(['auth']);

/* >>>>>>>>>>>>>>>>>>>>> Forms answers routes <<<<<<<<<<<<<<<<<<<< */
Route::inertia('/answers', 'Answers/Index')->middleware(['auth', 'isAdmin'])->name('answers.index.view');
Route::inertia('/answers/{answer}', 'Answers/Show')->middleware(['auth', 'isAdmin'])->name('answers.show.view');
Route::resource('api/answers', \App\Http\Controllers\FormAnswersController::class, [
    'as' => 'api'
])->middleware('auth');

/* >>>>>>>>>>>>>>>>>>>>>>>>>>Academic Periods routes <<<<<<<<<<<<<<<<<<<< */
Route::inertia('/academicPeriods', 'AcademicPeriods/Index')->middleware(['auth', 'isAdmin'])->name('academicPeriods.index.view');
Route::resource('api/academicPeriods', \App\Http\Controllers\AcademicPeriodController::class, [
    'as' => 'api'
])->middleware('auth');
//Sync periods from SIGA
Route::post('/api/academicPeriods/sync', [\App\Http\Controllers\AcademicPeriodController::class, 'sync'])->middleware(['auth'])->name('api.academicPeriods.sync');


/* >>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Units routes <<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/units', 'Unities/Index')->middleware(['auth', 'isAdmin'])->name('unities.index.view');
Route::resource('api/units', \App\Http\Controllers\UnitController::class, [
    'as' => 'api'])->middleware('auth');

Route::post('/api/units/sync', [\App\Http\Controllers\UnitController::class, 'sync'])->middleware(['auth'])->name('api.units.sync');
Route::post('/api/units/assign', [\App\Http\Controllers\UnitController::class, 'assign'])->middleware(['auth'])->name('api.units.assign');
Route::post('/api/units/transfer', [\App\Http\Controllers\UnitController::class, 'transferTeacherToUnit'])->middleware(['auth'])->name('api.units.transfer');

//Sync staffMembers
Route::post('api/staffMembers/sync', [\App\Http\Controllers\UnitController::class, 'syncStaffMembers'])->middleware(['auth'])->name('api.staffMembers.sync');
Route::get('staffMembers/index', [\App\Http\Controllers\UnitController::class, 'getStaffMembersFromDB'])->middleware(['auth'])->name('staffMembers.index');



//assignUnitAdmin
Route::post('/api/units/assignUnitAdmin', [\App\Http\Controllers\UnitController::class, 'assignUnitAdmin'])->middleware(['auth'])->name('api.units.assignUnitAdmin');

Route::post('/api/units/assignUnitBoss', [\App\Http\Controllers\UnitController::class, 'assignUnitBoss'])->middleware(['auth'])->name('api.units.assignUnitBoss');


//getUnitAdmin
Route::post('/units/unitAdmin', [\App\Http\Controllers\UnitController::class, 'getUnitAdmin'])->middleware(['auth'])->name('units.unitAdmin.index');


Route::post('unit/deleteUnitAdmin', [\App\Http\Controllers\UnitController::class, 'deleteUnitAdmin'])
    ->middleware(['auth', 'isAdmin'])->name('unit.deleteUnitAdmin');

Route::post('unit/deleteUnitBoss', [\App\Http\Controllers\UnitController::class, 'deleteUnitBoss'])->middleware(['auth', 'isAdmin'])->name('unit.deleteUnitBoss');

Route::post('unit/confirmDeleteUnitBoss', [\App\Http\Controllers\UnitController::class, 'confirmDeleteUnitBoss'])->middleware(['auth', 'isAdmin'])->name('unit.confirmDeleteUnitBoss');


//TODO PARA NO OLVIDAR
Route::get('/units/{unit}', [\App\Http\Controllers\UnitController::class, 'edit'])->middleware(['auth', 'isAdmin'])->name('units.manageUnit');


Route::get('unit/{unitId}/users', [\App\Http\Controllers\UnitController::class, 'show'])->name('unit.users')->middleware(['auth']);

Route::get('unit/{unitId}/teachers', [\App\Http\Controllers\UnitController::class, 'getUnitTeachers'])->name('unit.teachers')->middleware(['auth']);

Route::get('unit/{unitId}/adminsAndBosses', [\App\Http\Controllers\UnitController::class, 'getUnitAdminsAndBosses'])->name('unit.adminsAndBosses')->middleware(['auth']);

Route::get('unit/{unitId}/bosses', [\App\Http\Controllers\UnitController::class, 'getUnitBosses'])->name('unit.bosses')->middleware(['auth']);

Route::get('unit/{unitId}/unitAdmins', [\App\Http\Controllers\UnitController::class, 'getUnitAdmins'])->name('unit.unitAdmins')->middleware(['auth']);


Route::get('/units/{unitId}/manage', [\App\Http\Controllers\UnitController::class, 'manageRoles'])->middleware(['auth', 'isAdmin'])->name('units.roles.manage');


Route::get('/units/{unitId}/assessmentStatus', [\App\Http\Controllers\UnitController::class, 'assessmentStatus'])->middleware(['auth', 'isAdmin'])->name('units.assessment.status');


Route::get('/api/suitableTeachers', [\App\Http\Controllers\UnitController::class, 'getSuitableTeachers'])->middleware(['auth'])->name('api.suitableTeachers');




/* >>>>>>>>>>>>>>>>>>>>>>>>> Unity Assessment routes <<<<<<<<<<<<<<<<<<<<<<<<< */


Route::post('/unity/assignRoles', [\App\Http\Controllers\UnityAssessmentController::class, 'store'])->middleware(['auth'])->name('unity.roles.assignment');

Route::get('api/unity/allAssignments', [\App\Http\Controllers\UnityAssessmentController::class, 'index'])->middleware(['auth'])->name('api.unity.roles.assignment');

Route::post('api/unity/unitAssignments', [\App\Http\Controllers\UnityAssessmentController::class, 'getUnitAssignments'])->middleware(['auth'])->name('api.unity.roles.unitAssignments');

Route::post('/unity/removeAssignment', [\App\Http\Controllers\UnityAssessmentController::class, 'removeAssignment'])->middleware(['auth'])->name('unity.roles.removeAssignment');




/* >>>>>>>>>>>>>>>>>>>>>>>>> Service Areas routes <<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/serviceAreas', 'ServiceAreas/Index')->middleware(['auth', 'isAdmin'])->name('serviceAreas.index.view');
Route::resource('api/serviceAreas', \App\Http\Controllers\ServiceAreaController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/serviceAreas/sync', [\App\Http\Controllers\ServiceAreaController::class, 'sync'])->middleware(['auth'])->name('api.serviceAreas.sync');
Route::get('borrarForm/{form}', [\App\Http\Controllers\FormController::class, 'destroy']);

/* >>>>>>>>>>>>>>>>>>>>>>>>> Groups routes <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/groups', 'Groups/Index')->middleware(['auth', 'isAdmin'])->name('groups.index.view');
Route::inertia('groups/{groupId}/enrolls', 'Groups/Enrolls')->middleware(['auth'])->name('groups.enrolls.view');
Route::get('/api/groups/withoutTeacher', [\App\Http\Controllers\GroupController::class, 'getWithoutTeacher'])->middleware(['auth'])->name('api.groups.withoutTeacher');
//Sync groups
Route::post('/api/groups/sync', [\App\Http\Controllers\GroupController::class, 'sync'])->middleware(['auth'])->name('api.groups.sync');
//Group enrolls
Route::get('/api/groups/{groupId}/enrolls', [\App\Http\Controllers\GroupController::class, 'getEnrolls'])->middleware(['auth'])->name('api.groups.enrolls');
//Group general API
Route::resource('api/groups', \App\Http\Controllers\GroupController::class, [
    'as' => 'api'
])->middleware('auth');


/* >>>>>>>>>>>>>>>>>>>>>>> Enrolls routes <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/enrolls', 'Enrolls/Index')->middleware(['auth', 'isAdmin'])->name('enrolls.index.view');
//Create fake unites
Route::get('/api/enrolls/sync', [\App\Http\Controllers\EnrollController::class, 'sync'])->middleware(['auth']);
Route::resource('api/enrolls', \App\Http\Controllers\EnrollController::class, [
    'as' => 'api'
])->middleware('auth');
//Sync groups
Route::post('/api/enrolls/sync', [\App\Http\Controllers\EnrollController::class, 'sync'])->middleware(['auth'])->name('api.enrolls.sync');


/* >>>>>>>>>>>>>>>>>>>>>>> Teacher routes <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/teachers', 'Teachers/Index')->middleware(['auth', 'isAdmin'])->name('teachers.index.view');
//Change teacher status
Route::post('api/teachers/{teacher}/status', [\App\Http\Controllers\TeacherProfileController::class, 'changeStatus'])->middleware(['auth'])->name('api.teachers.changeStatus');
Route::resource('api/teachers', \App\Http\Controllers\TeacherProfileController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/teachers/sync', [\App\Http\Controllers\TeacherProfileController::class, 'sync'])->middleware(['auth'])->name('api.teachers.sync');
Route::get('/teachers/suitableList', [\App\Http\Controllers\TeacherProfileController::class, 'getSuitableList'])->middleware(['auth'])->name('teachers.getSuitableList');



/* >>>>>>>>>>>>>>>>>>>>>>>>StaffMembers routes <<<<<<<<<<<<<<<<<<<<<<<<<<<< */


/* >>>>>>>>>>>>>>>>>>>>>>>>>>>> Test routes  (students) <<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::get('/tests', [\App\Http\Controllers\TestsController::class, 'indexView'])->middleware(['auth'])->name('tests.index.view');
Route::post('/tests/{testId}', [\App\Http\Controllers\TestsController::class, 'startTest'])->middleware(['auth'])->name('tests.startTest');
Route::get('/tests/{testId}/preview', [\App\Http\Controllers\TestsController::class, 'preview'])->middleware(['auth'])->name('tests.preview');

//Change teacher status
Route::resource('api/tests', \App\Http\Controllers\TestsController::class, [
    'as' => 'api'
])->middleware('auth');


/* >>>>>>>>>>>>>>>>>>>>>>>>Roles routes <<<<<<<<<<<<<<<<<<<<<<<<<<<< */
//Get all roles
Route::get('/roles', [\App\Http\Controllers\Roles\RoleController::class, 'index'])->middleware(['auth', 'isAdmin'])->name('roles.index');
Route::resource('api/roles', \App\Http\Controllers\Roles\ApiRoleController::class, [
    'as' => 'api'
])->middleware('auth');

/* >>>>>>>>>>>>>>>>>>>>>>>>>>>>User routes <<<<<<<<<<<<<<<<<<<<<<<< */
//Get all users
Route::get('/users', [\App\Http\Controllers\Users\UserController::class, 'index'])->middleware(['auth', 'isAdmin'])->name('users.index');
//users api
Route::resource('api/users', \App\Http\Controllers\Users\ApiUserController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/users/{userId}/impersonate', [\App\Http\Controllers\Users\UserController::class, 'impersonate'])->middleware(['auth', 'isAdmin'])->name('users.impersonate');

//Update user role
Route::patch('/api/users/{user}/roles', [\App\Http\Controllers\Users\ApiUserController::class, 'updateUserRoles'])->middleware('auth')->name('api.users.roles.update');
Route::get('/api/users/{user}/roles', [\App\Http\Controllers\Users\ApiUserController::class, 'getUserRoles'])->middleware('auth')->name('api.users.roles.show');
Route::post('/api/roles/select', [\App\Http\Controllers\Users\ApiUserController::class, 'selectRole'])->middleware('auth')->name('api.roles.selectRole');

/* >>>>>>>>>>>>>>>>>>>>>>>>>>>> Auth routes <<<<<<<<<<<<<<<<<<<<<<<< */
Route::get('/', [\App\Http\Controllers\AuthController::class, 'handleRoleRedirect'])->middleware(['auth'])->name('redirect');
Route::get('/login', [\App\Http\Controllers\AuthController::class, 'redirectGoogleLogin'])->name('login');
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/google/callback', [\App\Http\Controllers\AuthController::class, 'handleGoogleCallback']);
Route::get('/pickRole', [\App\Http\Controllers\AuthController::class, 'pickRole'])->name('pickRole');


Route::get('/migrateToV2', function () {


    $units = DB::table('units')->get();
    $now = \Carbon\Carbon::now()->toDateTimeString();

    foreach ($units as $unit) {

        $unitIdentifier = $unit->code . "-" . $unit->assessment_period_id;

        DB::table('v2_units')
            ->insert(
                [
                    'identifier' => $unitIdentifier,
                    'code' => $unit->code,
                    'name' => $unit->name,
                    'assessment_period_id' => $unit->assessment_period_id,
                    'is_custom' => $unit->is_custom,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
    }

    $teacherProfiles = DB::table('teacher_profiles')->get();

    foreach ($teacherProfiles as $teacherProfile) {

        if($teacherProfile->unit_code==""){
            continue;
        }

        $unitIdentifier = $teacherProfile->unit_code . "-" . $teacherProfile->assessment_period_id;

        DB::table('v2_teacher_profiles')->insert(
            [
                'assessment_period_id' => $teacherProfile->assessment_period_id,
                'identification_number' => $teacherProfile->identification_number,
                'user_id' => $teacherProfile->user_id,
                'unit_identifier' => $unitIdentifier,
                'position' => $teacherProfile->position,
                'teaching_ladder' => $teacherProfile->teaching_ladder,
                'employee_type' => $teacherProfile->employee_type,
                'status' => $teacherProfile->status,
                'created_at' => $now,
                'updated_at' => $now
            ]
        );
    }

});

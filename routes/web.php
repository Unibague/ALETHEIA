<?php

use App\Exports\ResultsViewExport;
use App\Helpers\AtlanteProvider;
use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use App\Models\Enroll;

use App\Models\Form;
use App\Models\Group;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ProcessFormAnswersBatch;
/*
|--------------------------------------------------------------------------
| Administrator routes
|--------------------------------------------------------------------------
*/

/* >>>>>>>>>>>>>>>>>>>>>>>>>>>> Auth routes <<<<<<<<<<<<<<<<<<<<<<<< */
Route::get('/', [\App\Http\Controllers\AuthController::class, 'handleRoleRedirect'])->middleware(['auth'])->name('redirect');
Route::get('/login', [\App\Http\Controllers\AuthController::class, 'redirectGoogleLogin'])->name('login');
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/google/callback', [\App\Http\Controllers\AuthController::class, 'handleGoogleCallback']);
Route::get('/pickRole', [\App\Http\Controllers\AuthController::class, 'pickRole'])->name('pickRole');


/* >>>>>>>>>>>>>>>>>>>>>>>>>>Academic Periods routes <<<<<<<<<<<<<<<<<<<< */
Route::inertia('/academicPeriods', 'AcademicPeriods/Index')->middleware(['auth', 'isAdmin'])->name('academicPeriods.index.view');
Route::resource('api/academicPeriods', \App\Http\Controllers\AcademicPeriodController::class, [
    'as' => 'api'
])->middleware('auth');
//Sync periods from SIGA
Route::post('/api/academicPeriods/sync', [\App\Http\Controllers\AcademicPeriodController::class, 'sync'])->middleware(['auth'])->name('api.academicPeriods.sync');


/* >>>>>>>>>>>>>>>>>>>>>>>  Assessment Periods routes >>>>>>>><<<<<< */
Route::inertia('/assessmentPeriods', 'AssessmentPeriods/Index')->middleware(['auth', 'isAdmin'])->name('assessmentPeriods.index.view');
Route::resource('api/assessmentPeriods', \App\Http\Controllers\AssessmentPeriodController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/assessmentPeriods/{assessmentPeriod}/setActive', [\App\Http\Controllers\AssessmentPeriodController::class, 'setActive'])->middleware(['auth', 'isAdmin'])->name('api.assessmentPeriods.setActive');
Route::get('/assessmentPeriods/suitableTeachingLadders', [\App\Http\Controllers\AssessmentPeriodController::class, 'getSuitableTeachingLadders'])->middleware(['auth'])->name('api.assessmentPeriods.teachingLadders');

/* >>>>>>>>>>>>>>>>>>>>>>>  Assessment Ponderations >>>>>>>><<<<<< */
Route::inertia('/assessmentWeights', 'AssessmentWeights/Index')->middleware(['auth', 'isAdmin'])->name('assessmentWeights.index.view');
Route::resource('api/assessmentWeights', \App\Http\Controllers\AssessmentWeightController::class, [
    'as' => 'api'
])->middleware('auth');


Route::resource('api/competences', \App\Http\Controllers\CompetenceController::class, [
    'as' => 'api'
])->middleware('auth');



/* >>>>>>>>>>>>>>>>>>>>> Forms routes <<<<<<<<<<<<<<<<<<<< */
Route::get('api/forms/withoutQuestions', [\App\Http\Controllers\FormController::class, 'getWithoutQuestions'])->name('api.forms.withoutQuestions')->middleware(['auth', 'isAdmin']);
Route::get('api/forms/copyFromPeriod/{assessmentPeriod}', [\App\Http\Controllers\FormController::class, 'copyFromPeriod'])->name('api.forms.copyFromPeriod')->middleware(['auth', 'isAdmin']);
Route::inertia('/forms', 'Forms/Index')->middleware(['auth', 'isAdmin'])->name('forms.index.view');
Route::inertia('/forms/{form}', 'Forms/Show')->middleware(['auth', 'isAdmin'])->name('forms.show.view');
Route::resource('api/forms', \App\Http\Controllers\FormController::class, [
    'as' => 'api'
])->middleware('auth');
Route::get('borrarForm/{form}', [\App\Http\Controllers\FormController::class, 'destroy']);
Route::post('api/forms/{form}/copy', [\App\Http\Controllers\FormController::class, 'copy'])->name('api.forms.copy')->middleware(['auth']);
Route::patch('api/forms/{form}/formQuestions', [\App\Http\Controllers\FormQuestionController::class, 'storeOrUpdate'])->name('api.forms.questions.store')->middleware(['auth']);
Route::get('api/forms/{form}/formQuestions', [\App\Http\Controllers\FormQuestionController::class, 'getByFormId'])->name('api.forms.questions.show')->middleware(['auth']);

/* >>>>>>>>>>>>>>>>>>>>> Forms answers routes <<<<<<<<<<<<<<<<<<<< */
Route::inertia('/answers', 'Answers/Index')->middleware(['auth', 'isAdmin'])->name('answers.index.view');

Route::inertia('/answers/{answer}', 'Answers/Show')->middleware(['auth', 'isAdmin'])->name('answers.show.view');
Route::resource('api/answers', \App\Http\Controllers\FormAnswersController::class, [
    'as' => 'api'
])->middleware('auth');
Route::get('formAnswers/teachers', [\App\Http\Controllers\FormAnswersController::class, 'getTeacherAnswers'])->name('formAnswers.teachers.show')->middleware(['auth']);
Route::get('formAnswers/studentPerspective', [\App\Http\Controllers\FormAnswersController::class, 'getStudentAnswers'])->name('formAnswers.studentPerspective')->middleware(['auth']);
Route::get('formAnswers/teachers/finalGrades', [\App\Http\Controllers\FormAnswersController::class, 'getFinalGrades'])->name('formAnswers.finalGrades')->middleware(['auth']);
Route::post('formAnswers/teachers/openAnswersStudents', [\App\Http\Controllers\FormAnswersController::class, 'getOpenAnswersStudents'])->name('formAnswers.teachers.openAnswersStudents')->middleware(['auth']);
Route::post('formAnswers/teachers/openAnswersStudentsGroup', [\App\Http\Controllers\FormAnswersController::class, 'getOpenAnswersStudentsFromGroup'])->name('formAnswers.teachers.openAnswersStudentsFromGroup')->middleware(['auth']);
Route::post('formAnswers/teachers/openAnswersColleagues', [\App\Http\Controllers\FormAnswersController::class, 'getOpenAnswersColleagues'])->name('formAnswers.teachers.openAnswersColleagues')->middleware(['auth']);

/* >>>>>>>>>>>>>>>>>>>>>>>>> Service Areas routes <<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/serviceAreas', 'ServiceAreas/Index')->middleware(['auth', 'isAdmin'])->name('serviceAreas.index.view');
Route::resource('api/serviceAreas', \App\Http\Controllers\ServiceAreaController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/serviceAreas/sync', [\App\Http\Controllers\ServiceAreaController::class, 'sync'])->middleware(['auth'])->name('api.serviceAreas.sync');

Route::get('serviceAreas/getServiceAreasResults', [\App\Http\Controllers\ServiceAreaController::class, 'getServiceAreasResults'])->middleware(['auth'])->name('serviceAreas.getResults');
Route::get('serviceAreas/getServiceAreasResultsPerGroup', [\App\Http\Controllers\ServiceAreaController::class, 'getServiceAreasResultsPerGroup'])->middleware(['auth'])->name('serviceAreas.getResultsPerGroup');
Route::get('serviceAreas/getTeachersWithResults', [\App\Http\Controllers\ServiceAreaController::class, 'getServiceAreasTeachersWithResults'])
    ->middleware(['auth'])->name('serviceAreas.teachersWithResults');
Route::get('/serviceAreas/{serviceAreaCode}', [\App\Http\Controllers\ServiceAreaController::class, 'edit'])->middleware(['auth', 'isAdminOrUnitAdmin'])->name('serviceAreas.manageServiceArea');
Route::get('serviceArea/{serviceAreaCode}/admins', [\App\Http\Controllers\ServiceAreaController::class, 'getServiceAreaAdmins'])->name('serviceArea.admins')->middleware(['auth']);
Route::post('serviceArea/assignAdmin', [\App\Http\Controllers\ServiceAreaController::class, 'assignServiceAreaAdmin'])->name('serviceArea.assignAdmin')->middleware(['auth']);
Route::post('serviceArea/deleteAdmin', [\App\Http\Controllers\ServiceAreaController::class, 'deleteServiceAreaAdmin'])->name('serviceArea.deleteAdmin')->middleware(['auth']);


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
Route::get('groups/purify', [\App\Http\Controllers\GroupController::class, 'purify'])->middleware(['auth'])->name('groups.purify');


/* >>>>>>>>>>>>>>>>>>>>>>> Enrolls routes <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/enrolls', 'Enrolls/Index')->middleware(['auth', 'isAdmin'])->name('enrolls.index.view');
//Create fake unites
Route::get('/api/enrolls/sync', [\App\Http\Controllers\EnrollController::class, 'sync'])->middleware(['auth']);
Route::resource('api/enrolls', \App\Http\Controllers\EnrollController::class, [
    'as' => 'api'
])->middleware('auth');
//Sync groups
Route::post('/api/enrolls/sync', [\App\Http\Controllers\EnrollController::class, 'sync'])->middleware(['auth'])->name('api.enrolls.sync');
Route::post('/api/enrolls/deleteThoseGroups', [\App\Http\Controllers\EnrollController::class, 'deleteThoseExistingDuplicatedGroups'])->middleware(['auth'])->name('api.enrolls.deleteThoseGroups');


/* >>>>>>>>>>>>>>>>>>>>>>>>StaffMembers routes <<<<<<<<<<<<<<<<<<<<<<<<<<<< */


/* >>>>>>>>>>>>>>>>>>>>>>>>>>>> Reports routes <<<<<<<<<<<<<<<<<<<<<<<< */
/*Route::get('/reports/showCompleteServiceAreas', [\App\Http\Controllers\ReportsController::class, 'index'])->middleware(['auth', 'isAdmin'])->name('reports.showCompleteServiceAreas');*/
/*Route::inertia('/reports/showComplete360', 'Reports/Complete360AssessmentResults')->middleware(['auth'])->name('reports.showComplete360');*/
Route::get('/reports/show360Assessment', [\App\Http\Controllers\ReportsController::class, 'show360Assessment'])->middleware(['auth'])->name('reports.show360Assessment');
Route::get('/reports/showServiceAreasAssessment', [\App\Http\Controllers\ReportsController::class, 'showServiceAreasAssessment'])->middleware(['auth'])->name('reports.showServiceAreasAssessment');
/*Route::get('/reports/showServiceAreaGroupsAssessment', [\App\Http\Controllers\ReportsController::class, 'showServiceAreaGroupsAssessment'])->middleware(['auth'])->name('reports.showServiceAreaGroupsAssessment');
Route::inertia('/reports/showCompleteServiceAreas', 'Reports/CompleteServiceAreasResults')->middleware(['auth', 'isAdmin'])->name('reports.showCompleteServiceAreas');
Route::inertia('/reports/showCompleteServiceAreasGroup', 'Reports/CompleteServiceAreasGroupResults')->middleware(['auth', 'isAdmin'])->name('reports.showCompleteServiceAreasGroup');
Route::get('/reports/showFaculty', [\App\Http\Controllers\ReportsController::class, 'showFaculty'])->middleware(['auth'])->name('reports.showFaculty');
Route::get('/reports/showServiceArea', [\App\Http\Controllers\ReportsController::class, 'showServiceArea'])->middleware(['auth'])->name('reports.showServiceArea');
Route::get('/reports/showServiceAreaGroup', [\App\Http\Controllers\ReportsController::class, 'showServiceAreaGroup'])->middleware(['auth'])->name('reports.showServiceAreaGroup');*/
Route::get('/reports/chart/{chartInfo}/teacher/{teacherResults}/downloadPdf', [\App\Http\Controllers\ReportsController::class, 'downloadPDF'])->middleware(['auth'])->name('reports.index.downloadPdf');
Route::get('/reports/serviceArea/chart/{chartInfo}/teacher/{teacherResults}/downloadPdf', [\App\Http\Controllers\ReportsController::class, 'downloadServiceAreaPDF'])->middleware(['auth'])->name('reports.serviceArea.index.downloadPdf');

Route::post('/reports/360Assessment/downloadPdf', [\App\Http\Controllers\ReportsController::class, 'download360Report'])->middleware(['auth'])->name('reports.assessment360');
Route::post('/reports/serviceAreasAssessment/downloadPdf', [\App\Http\Controllers\ReportsController::class, 'downloadServiceAreasReport'])
    ->middleware(['auth'])->name('reports.serviceAreas');

/* >>>>>>>>>>>>>>>>>>>>>>>>Reminders routes <<<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/reminders', 'Reminders/Index')->middleware(['auth', 'isAdmin'])->name('reminders.index');
Route::get('/reminders/get', [\App\Http\Controllers\ReportsController::class, 'getReminders'])->middleware(['auth'])->name('reminders.get');
Route::post('/reminders/update', [\App\Http\Controllers\ReportsController::class, 'updateReminder'])->middleware(['auth'])->name('reminders.update');


/* >>>>>>>>>>>>>>>>>>>>>>>>ResponseIdeals routes <<<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/responseIdeals', 'ResponseIdeals/IndexAndEdit')->middleware(['auth', 'isAdmin'])->name('responseIdeals.index.view');
Route::post('teacher/responseIdeals/get', [\App\Http\Controllers\ResponseIdealController::class, 'getTeacherResponseIdeals'])->middleware('auth')->name('teacher.responseIdeals.get');
Route::get('{unitId}/responseIdeals/get', [\App\Http\Controllers\ResponseIdealController::class, 'getUnitResponseIdeals'])->middleware('auth')->name('unit.responseIdeals.get');
Route::get('/responseIdeals/index/{unitId}', [\App\Http\Controllers\ResponseIdealController::class, 'indexUnitResponseIdeals'])->middleware(['auth', 'isAdmin'])->name('unit.responseIdeals.index');
Route::get('/responseIdeals/edit/{unitId}/{teachingLadder}', [\App\Http\Controllers\ResponseIdealController::class, 'editUnitTeachingLadderResponseIdeals'])->middleware(['auth', 'isAdmin'])->name('unit.teachingLadder.responseIdeals.edit');
Route::get('/responseIdeals/get/{unitId}/{teachingLadder}', [\App\Http\Controllers\ResponseIdealController::class, 'getUnitTeachingLadderResponseIdeals'])->middleware(['auth', 'isAdmin'])->name('unit.teachingLadder.responseIdeals.get');
Route::get('/responseIdeals/get', [\App\Http\Controllers\ResponseIdealController::class, 'getAllCompetences'])->middleware('auth')->name('responseIdeals.get');
Route::post('/responseIdeals/update', [\App\Http\Controllers\ResponseIdealController::class, 'upsertData'])->middleware('auth')->name('responseIdeals.update');
Route::post('/responseIdeals/delete', [\App\Http\Controllers\ResponseIdealController::class, 'delete'])->middleware('auth')->name('responseIdeals.delete');


/* >>>>>>>>>>>>>>>>>>>>>>>>Roles routes <<<<<<<<<<<<<<<<<<<<<<<<<<<< */
//Get all roles
Route::get('/roles', [\App\Http\Controllers\Roles\RoleController::class, 'index'])->middleware(['auth', 'isAdmin'])->name('roles.index');
Route::post('/roleName', [\App\Http\Controllers\Roles\RoleController::class, 'getNameByCustomId'])->middleware(['auth'])->name('role.name');
Route::resource('api/roles', \App\Http\Controllers\Roles\ApiRoleController::class, [
    'as' => 'api'
])->middleware('auth');


/* >>>>>>>>>>>>>>>>>>>>>>> Teacher routes <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/teachers', 'Teachers/Index')->middleware(['auth', 'isAdmin'])->name('teachers.index.view');
//Change teacher status
Route::post('api/teachers/{teacher}/status', [\App\Http\Controllers\TeacherProfileController::class, 'changeStatus'])->middleware(['auth'])->name('api.teachers.changeStatus');
Route::resource('api/teachers', \App\Http\Controllers\TeacherProfileController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/teachers/sync', [\App\Http\Controllers\TeacherProfileController::class, 'sync'])->middleware(['auth'])->name('api.teachers.sync');
Route::get('/teachers/suitableList', [\App\Http\Controllers\TeacherProfileController::class, 'getSuitableList'])->middleware(['auth'])->name('teachers.getSuitableList');
/*Route::inertia('/teachers/assessments', 'Teachers/Assessments')->middleware(['auth', 'isAdmin'])->name('teachers.assessments.view');*/
Route::get('/teachers/assessments', [\App\Http\Controllers\TeacherProfileController::class, 'viewTeacherAssessments'])->middleware(['auth', 'isTeacher'])->name('teachers.assessments.view');
Route::post('api/teachers/teachingLadder', [\App\Http\Controllers\TeacherProfileController::class, 'getTeachingLadderByUserId'])->middleware(['auth'])->name('teachers.getTeachingLadder');


/* >>>>>>>>>>>>>>>>>>>>>>>>>>>> Test routes  (students) <<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::get('/tests', [\App\Http\Controllers\TestsController::class, 'indexView'])->middleware(['auth'])->name('tests.index.view');
Route::post('/tests/{testId}', [\App\Http\Controllers\TestsController::class, 'startTest'])->middleware(['auth'])->name('tests.startTest');
Route::get('/tests/{testId}/preview', [\App\Http\Controllers\TestsController::class, 'preview'])->middleware(['auth'])->name('tests.preview');
//Change teacher status
Route::resource('api/tests', \App\Http\Controllers\TestsController::class, [
    'as' => 'api'])->middleware('auth');
Route::get('/test/teacherAutoTest', [\App\Http\Controllers\TestsController::class, 'indexTeacherAutoTest'])->middleware(['auth'])->name('tests.index.teacherAutoTest');
Route::get('/test/teacherPeerTests', [\App\Http\Controllers\TestsController::class, 'indexTeacherPeerTests'])->middleware(['auth'])->name('tests.index.teacherPeerTests');
Route::get('/test/teacherBossTests', [\App\Http\Controllers\TestsController::class, 'indexTeacherBossTests'])->middleware(['auth'])->name('tests.index.teacherBossTests');


/* >>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Units routes <<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/units', 'Unities/Index')->middleware(['auth', 'isAdminOrUnitAdmin'])->name('unities.index.view');
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
    ->middleware(['auth', 'isAdminOrUnitAdmin'])->name('unit.deleteUnitAdmin');
Route::post('unit/deleteUnitBoss', [\App\Http\Controllers\UnitController::class, 'deleteUnitBoss'])->middleware(['auth', 'isAdminOrUnitAdmin'])->name('unit.deleteUnitBoss');
Route::post('unit/confirmDeleteUnitBoss', [\App\Http\Controllers\UnitController::class, 'confirmDeleteUnitBoss'])->middleware(['auth', 'isAdminOrUnitAdmin'])->name('unit.confirmDeleteUnitBoss');
Route::get('/units/{unit}', [\App\Http\Controllers\UnitController::class, 'edit'])->middleware(['auth', 'isAdminOrUnitAdmin'])->name('units.manageUnit');
Route::get('unit/{unitId}/users', [\App\Http\Controllers\UnitController::class, 'show'])->name('unit.users')->middleware(['auth']);
Route::get('unit/{unitId}/teachers', [\App\Http\Controllers\UnitController::class, 'getUnitTeachers'])->name('unit.teachers')->middleware(['auth']);
Route::get('unit/{unitId}/adminsAndBosses', [\App\Http\Controllers\UnitController::class, 'getUnitAdminsAndBosses'])->name('unit.adminsAndBosses')->middleware(['auth']);
Route::get('unit/{unitId}/bosses', [\App\Http\Controllers\UnitController::class, 'getUnitBosses'])->name('unit.bosses')->middleware(['auth']);
Route::get('unit/{unitId}/unitAdmins', [\App\Http\Controllers\UnitController::class, 'getUnitAdmins'])->name('unit.unitAdmins')->middleware(['auth']);
Route::get('/units/{unit}/manage', [\App\Http\Controllers\UnitController::class, 'manageRoles'])->middleware(['auth', 'isAdminOrUnitAdmin'])->name('units.roles.manage');
Route::get('/units/{unit}/assessmentStatus', [\App\Http\Controllers\UnitController::class, 'assessmentStatus'])->middleware(['auth', 'isAdminOrUnitAdmin'])->name('units.assessment.status');
Route::get('/api/suitableTeachers', [\App\Http\Controllers\UnitController::class, 'getSuitableTeachers'])->middleware(['auth'])->name('api.suitableTeachers');
Route::get('units/teachers/assigned', [\App\Http\Controllers\UnitController::class, 'getAssignedTeachers'])->middleware(['auth', 'isAdminOrUnitAdmin'])->name('units.teachers.assigned');
Route::get('unit/faculties', [\App\Http\Controllers\UnitController::class, 'getAllFaculties'])->middleware(['auth', 'isAdminOrUnitAdmin'])->name('unit.getFaculties');


/* >>>>>>>>>>>>>>>>>>>>>>>>> Unity Assessment routes <<<<<<<<<<<<<<<<<<<<<<<<< */
Route::post('/unity/assignRoles', [\App\Http\Controllers\UnityAssessmentController::class, 'store'])->middleware(['auth'])->name('unity.roles.assignment');

Route::get('api/unity/allAssignments', [\App\Http\Controllers\UnityAssessmentController::class, 'index'])->middleware(['auth'])->name('api.unity.roles.assignment');
Route::post('api/unity/unitAssignments', [\App\Http\Controllers\UnityAssessmentController::class, 'getUnitAssignments'])->middleware(['auth'])->name('api.unity.roles.unitAssignments');
Route::get('unit/unityAssessments', [\App\Http\Controllers\UnityAssessmentController::class, 'getUnitAssessments'])->middleware(['auth'])->name('api.unit.assessments');
Route::post('/unity/removeAssignment', [\App\Http\Controllers\UnityAssessmentController::class, 'removeAssignment'])->middleware(['auth'])->name('unity.roles.removeAssignment');
Route::post('unity/autoAssessment', [\App\Http\Controllers\UnityAssessmentController::class, 'getAutoAssessment'])->middleware(['auth', 'isTeacher'])->name('api.unity.getAutoAssessment');
Route::post('unity/peerAssessments', [\App\Http\Controllers\UnityAssessmentController::class, 'getPeerAssessments'])->middleware(['auth', 'isTeacher'])->name('api.unity.peerAssessments');
Route::post('unity/BossAssessments', [\App\Http\Controllers\UnityAssessmentController::class, 'getBossAssessments'])->middleware(['auth', 'isTeacher'])->name('api.unity.bossAssessments');


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


Route::get('/fulfillServiceAreasResultsTable', function () {

    $activeAssessmentPeriodId = \App\Models\AssessmentPeriod::getActiveAssessmentPeriod()->id;

    $teachers = DB::table('group_results')->select(['teacher_id'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get()->toArray();

    $uniqueTeachers = array_column($teachers, 'teacher_id');

    $uniqueTeachersId = array_unique($uniqueTeachers);


    foreach ($uniqueTeachersId as $uniqueTeacherId) {

        $serviceAreaCodesFromTeacher = DB::table('group_results')->select(['service_area_code'])->where('teacher_id', '=', $uniqueTeacherId)->get()->toArray();

        $uniqueServiceAreaCodes = array_column($serviceAreaCodesFromTeacher, 'service_area_code');

        $uniqueServiceAreaCodes = array_unique($uniqueServiceAreaCodes);


        foreach ($uniqueServiceAreaCodes as $uniqueServiceAreaCode) {

            $groupsFromServiceAreaCode = DB::table('group_results')->where('service_area_code', '=', $uniqueServiceAreaCode)
                ->where('teacher_id', '=', $uniqueTeacherId)->get();

            $groupsAmountFromServiceAreaCode = count($groupsFromServiceAreaCode);

            $aggregateTotalStudentsReviewersOnServiceArea = 0;
            $aggregateTotalStudentsEnrolledOnServiceArea = 0;


            $final_first_aggregate_competence_average = 0;
            $final_second_aggregate_competence_average = 0;
            $final_third_aggregate_competence_average = 0;
            $final_fourth_aggregate_competence_average = 0;
            $final_fifth_aggregate_competence_average = 0;
            $final_sixth_aggregate_competence_average = 0;


            foreach ($groupsFromServiceAreaCode as $key => $groupFromServiceAreaCode) {

                $aggregateTotalStudentsReviewersOnServiceArea += $groupsFromServiceAreaCode[$key]->students_amount_reviewers;
                $aggregateTotalStudentsEnrolledOnServiceArea += $groupsFromServiceAreaCode[$key]->students_amount_on_group;

                $final_first_aggregate_competence_average += $groupFromServiceAreaCode->first_final_competence_average;
                $final_second_aggregate_competence_average += $groupFromServiceAreaCode->second_final_competence_average;
                $final_third_aggregate_competence_average += $groupFromServiceAreaCode->third_final_competence_average;
                $final_fourth_aggregate_competence_average += $groupFromServiceAreaCode->fourth_final_competence_average;
                $final_fifth_aggregate_competence_average += $groupFromServiceAreaCode->fifth_final_competence_average;
                $final_sixth_aggregate_competence_average += $groupFromServiceAreaCode->sixth_final_competence_average;

            }


            $final_first_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
            $final_second_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
            $final_third_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
            $final_fourth_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
            $final_fifth_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
            $final_sixth_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;

            $final_first_aggregate_competence_average = number_format($final_first_aggregate_competence_average, 1);
            $final_second_aggregate_competence_average = number_format($final_second_aggregate_competence_average, 1);
            $final_third_aggregate_competence_average = number_format($final_third_aggregate_competence_average, 1);
            $final_fourth_aggregate_competence_average = number_format($final_fourth_aggregate_competence_average, 1);
            $final_fifth_aggregate_competence_average = number_format($final_fifth_aggregate_competence_average, 1);
            $final_sixth_aggregate_competence_average = number_format($final_sixth_aggregate_competence_average, 1);


            DB::table('teachers_service_areas_results')->updateOrInsert(['teacher_id' => $uniqueTeacherId, 'service_area_code' => $uniqueServiceAreaCode, 'assessment_period_id' => $activeAssessmentPeriodId],
                ['first_final_aggregate_competence_average' => $final_first_aggregate_competence_average,
                    'second_final_aggregate_competence_average' => $final_second_aggregate_competence_average,
                    'third_final_aggregate_competence_average' => $final_third_aggregate_competence_average,
                    'fourth_final_aggregate_competence_average' => $final_fourth_aggregate_competence_average,
                    'fifth_final_aggregate_competence_average' => $final_fifth_aggregate_competence_average,
                    'sixth_final_aggregate_competence_average' => $final_sixth_aggregate_competence_average,
                    'aggregate_students_amount_reviewers' => $aggregateTotalStudentsReviewersOnServiceArea,
                    'aggregate_students_amount_on_service_area' => $aggregateTotalStudentsEnrolledOnServiceArea,
                    'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                    'updated_at' => Carbon::now('GMT-5')->toDateTimeString()]);


        }

    }

});


Route::get('/migrateLegacyRecordsFormAnswersTable', function () {

    $formAnswers = DB::table('form_answers as fa')->where('assessment_period_id', '=', 5)
        ->where('competences_average','=',null)->take(8000)->get();
    foreach ($formAnswers as $formAnswer) {
        $results = \App\Models\FormAnswers::getCompetencesAverage(json_decode($formAnswer->answers, JSON_THROW_ON_ERROR));
        $openEndedAnswers = \App\Models\FormAnswers::getOpenEndedAnswersFromFormAnswer(json_decode($formAnswer->answers, JSON_THROW_ON_ERROR));
        DB::table('form_answers as fa')->
        updateOrInsert(['id'=> $formAnswer->id], ['competences_average' => $results['competences']
        ,'overall_average' => $results['overall_average'], 'open_ended_answers' => $openEndedAnswers]);
    }
});

Route::get('/migrateLegacyRecordsGroupResultsTable', function () {

    //Get the ID's from the teachers that had answers on the active assessment period id
    $teacherIds = DB::table('form_answers as fa')->select(['fa.teacher_id'])
        ->join('forms as f', 'fa.form_id', '=', 'f.id')
        ->where('f.type', '=', 'estudiantes')
        ->where('fa.assessment_period_id','=', 6)->pluck('fa.teacher_id')->unique();

    //First, we insert the student assessments for each teacher on each group on group_results table (updateGroupResultsFromTeacher)
    if (count($teacherIds) > 0) {
        foreach ($teacherIds as $teacherId) {
            $groups = DB::table('groups as g')->where('g.teacher_id', '=', $teacherId)
                ->join('academic_periods as ap', 'g.academic_period_id', '=', 'ap.id')
                ->where('ap.assessment_period_id','=',6)->get();

            //Now that we have the groups info for the teacher, we can proceed and do the calculations
            foreach ($groups as $group) {

                $answersFromGroup = DB::table('form_answers as fa')
                    ->join('forms as f', 'fa.form_id', '=', 'f.id')
                    ->join('groups', 'fa.group_id', '=', 'groups.group_id')
                    ->where('f.type', '=', 'estudiantes')
                    ->where('fa.group_id', '=', $group->group_id)->get();

                $studentsWithAnswer = count($answersFromGroup);

                $studentsEnrolled = DB::table('group_user')
                    ->where('group_id', '=', $group->group_id)
                    ->count();

                if ($answersFromGroup->isEmpty() || $group->assessment_period_id == null) {
                    continue;
                }

                $openEndedAnswers = [];
                $competencesAverage = [];
                $competencesData = [];

                foreach ($answersFromGroup as $answerFromGroup) {

                    $userOpenEndedAnswers = json_decode($answerFromGroup->open_ended_answers, JSON_THROW_ON_ERROR);
                    $openEndedAnswers [] = $userOpenEndedAnswers;
                    $answerCompetences = json_decode($answerFromGroup->competences_average);

                    foreach ($answerCompetences as $competence) {

                        $competenceKey = $competence->name;
                        if (!isset($competencesData[$competence->name])) {
                            $competencesData[$competence->name] = [
                               'id' => $competence->id,
                               'totalScore' => 0,
                               'totalAnswers' => 0,
                               'attributes' => []
                            ];
                        }

                        $score = floatval($competence->average);
                        $competencesData[$competenceKey]['totalScore'] += $score;
                        $competencesData[$competenceKey]['totalAnswers'] ++;

                        if (isset($competence->attributes) && is_array($competence->attributes)) {
                            foreach ($competence->attributes as $attribute) {
                                $attributeKey = $attribute->name;
                                if (!isset($competencesData[$competenceKey]['attributes'][$attributeKey])) {
                                    $competencesData[$competenceKey]['attributes'][$attributeKey] = [
                                        'totalScore' => 0,
                                        'totalAnswers' => 0
                                    ];
                                }
                                $attributeScore = floatval($attribute->average);
                                $competencesData[$competenceKey]['attributes'][$attributeKey]['totalScore'] += $attributeScore;
                                $competencesData[$competenceKey]['attributes'][$attributeKey]['totalAnswers']++;
                            }
                        }
                    }


                }

                $groupedOpenEndedAnswers = \App\Models\FormAnswers::groupOpenEndedAnswers($openEndedAnswers);

                $overallAverage = 0;
                $competencesPresent = 0;
                foreach ($competencesData as $competenceName => $competence) {

                    $attributesAverage = [];
                    if ($competence['totalAnswers'] > 0) {
                        $competenceAverage = round($competence['totalScore'] / ($competence['totalAnswers']), 2);

                        foreach ($competence['attributes'] as $attributeName => $attribute) {
                            if ($attribute['totalAnswers'] > 0) {
                                $attributeAverage = round($attribute['totalScore'] / $attribute['totalAnswers'], 2);
                                $attributesAverage [] = [
                                    'name' => $attributeName,
                                    'overall_average' => $attributeAverage
                                ];
                            }
                        }

                        $competencesAverage[] = [
                            'id' => $competence['id'],
                            'name' => $competenceName,
                            'overall_average' => $competenceAverage,
                            'attributes' => $attributesAverage
                        ];

                        if($competenceName !== 'Satisfacción'){
                            $overallAverage += $competenceAverage;
                            $competencesPresent++;
                        }
                    }
                }

                // Calculate the final overall average
                if ($competencesPresent > 0) {
                    $overallAverage /= $competencesPresent;
                }

                DB::table('group_results')->updateOrInsert
                (
                    [
                        'teacher_id' => $teacherId,
                        'group_id' => $group->group_id,
                        'assessment_period_id' => $group->assessment_period_id
                    ],
                    [
                        'hour_type' => $group->hour_type,
                        'service_area_code' => $group->service_area_code,
                        'students_amount_reviewers' => $studentsWithAnswer,
                        'students_amount_on_group' => $studentsEnrolled,
                        'competences_average' => json_encode($competencesAverage, JSON_UNESCAPED_UNICODE),
                        'overall_average' => round($overallAverage, 2),
                        'open_ended_answers' => json_encode($groupedOpenEndedAnswers, JSON_UNESCAPED_UNICODE),
                        'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                        'updated_at' => Carbon::now('GMT-5')->toDateTimeString()
                    ]
                );
            }
        }
    }
});


//
//        //Now, we are going to calculate the final results on groups for teachers on 360 assessment, this is, only taking into account groups with hour_type = normal
//        $finalResultsFromTeachersOnGroups = DB::table('group_results as gr')->select(['gr.teacher_id'])
//            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get()->toArray();
//
//        $uniqueTeachers = array_column($finalResultsFromTeachersOnGroups, 'teacher_id');
//
//        $uniqueTeachers = array_unique($uniqueTeachers);
//
//        foreach ($uniqueTeachers as $uniqueTeacher) {
//
//            $finalResultsFromTeacherOnGroups = DB::table('group_results as gr')->where('teacher_id', '=', $uniqueTeacher)
//                ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get();
//
//            $groupsAmount = count($finalResultsFromTeacherOnGroups);
//
//            $aggregateTotalStudentsReviewersOnGroups = 0;
//            $aggregateTotalStudentsEnrolledOnGroups = 0;
//
//            $final_first_aggregate_competence_average = 0;
//            $final_second_aggregate_competence_average = 0;
//            $final_third_aggregate_competence_average = 0;
//            $final_fourth_aggregate_competence_average = 0;
//            $final_fifth_aggregate_competence_average = 0;
//            $final_sixth_aggregate_competence_average = 0;
//            /*
//                        if ($uniqueTeacher == 181){
//                            dd($finalResultsFromTeacherOnGroups);
//                        }*/
//            foreach ($finalResultsFromTeacherOnGroups as $key => $finalResultsFromTeacherOnGroup) {
//
//                $aggregateTotalStudentsReviewersOnGroups += $finalResultsFromTeacherOnGroups[$key]->students_amount_reviewers;
//                $aggregateTotalStudentsEnrolledOnGroups += $finalResultsFromTeacherOnGroups[$key]->students_amount_on_group;
//
//                $final_first_aggregate_competence_average += $finalResultsFromTeacherOnGroup->first_final_competence_average;
//                $final_second_aggregate_competence_average += $finalResultsFromTeacherOnGroup->second_final_competence_average;
//                $final_third_aggregate_competence_average += $finalResultsFromTeacherOnGroup->third_final_competence_average;
//                $final_fourth_aggregate_competence_average += $finalResultsFromTeacherOnGroup->fourth_final_competence_average;
//                $final_fifth_aggregate_competence_average += $finalResultsFromTeacherOnGroup->fifth_final_competence_average;
//                $final_sixth_aggregate_competence_average += $finalResultsFromTeacherOnGroup->sixth_final_competence_average;
//
//            }
//
//            /*    if ($uniqueTeacher == 181){
//
//                    dd($final_first_aggregate_competence_average,$final_second_aggregate_competence_average);
//                    dd($aggregateTotalStudentsReviewersOnGroups);
//
//                }*/
//            $final_first_aggregate_competence_average /= $groupsAmount;
//            $final_second_aggregate_competence_average /= $groupsAmount;
//            $final_third_aggregate_competence_average /= $groupsAmount;
//            $final_fourth_aggregate_competence_average /= $groupsAmount;
//            $final_fifth_aggregate_competence_average /= $groupsAmount;
//            $final_sixth_aggregate_competence_average /= $groupsAmount;
//
//            $final_first_aggregate_competence_average = number_format($final_first_aggregate_competence_average, 1);
//            $final_second_aggregate_competence_average = number_format($final_second_aggregate_competence_average, 1);
//            $final_third_aggregate_competence_average = number_format($final_third_aggregate_competence_average, 1);
//            $final_fourth_aggregate_competence_average = number_format($final_fourth_aggregate_competence_average, 1);
//            $final_fifth_aggregate_competence_average = number_format($final_fifth_aggregate_competence_average, 1);
//            $final_sixth_aggregate_competence_average = number_format($final_sixth_aggregate_competence_average, 1);
//
//            //This table is for 360 assessment teachers only.
//            DB::table('teachers_students_perspectives')->updateOrInsert(['teacher_id' => $uniqueTeacher, 'assessment_period_id' => $activeAssessmentPeriodId],
//                ['first_final_aggregate_competence_average' => $final_first_aggregate_competence_average,
//                    'second_final_aggregate_competence_average' => $final_second_aggregate_competence_average,
//                    'third_final_aggregate_competence_average' => $final_third_aggregate_competence_average,
//                    'fourth_final_aggregate_competence_average' => $final_fourth_aggregate_competence_average,
//                    'fifth_final_aggregate_competence_average' => $final_fifth_aggregate_competence_average,
//                    'sixth_final_aggregate_competence_average' => $final_sixth_aggregate_competence_average,
//                    'groups_amount' => $groupsAmount,
//                    'aggregate_students_amount_reviewers' => $aggregateTotalStudentsReviewersOnGroups,
//                    'aggregate_students_amount_on_360_groups' => $aggregateTotalStudentsEnrolledOnGroups,
//                    'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
//                    'updated_at' => Carbon::now('GMT-5')->toDateTimeString()]);
//        }
//
//        //Now, we are going to calculate the final results on groups for the teacher regarding only students assessments. This is, the results for the service_area report
//        $teachers = DB::table('group_results')->select(['teacher_id'])->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get()->toArray();
//        $uniqueTeachers = array_column($teachers, 'teacher_id');
//        $uniqueTeachersId = array_unique($uniqueTeachers);
//
//        foreach ($uniqueTeachersId as $uniqueTeacherId) {
//
//            $serviceAreaCodesFromTeacher = DB::table('group_results')->select(['service_area_code'])->where('teacher_id', '=', $uniqueTeacherId)
//                ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get()->toArray();
//            $uniqueServiceAreaCodes = array_column($serviceAreaCodesFromTeacher, 'service_area_code');
//            $uniqueServiceAreaCodes = array_unique($uniqueServiceAreaCodes);
//
//            foreach ($uniqueServiceAreaCodes as $uniqueServiceAreaCode) {
//
//                $groupsFromServiceAreaCode = DB::table('group_results')->where('service_area_code', '=', $uniqueServiceAreaCode)
//                    ->where('assessment_period_id', '=', $activeAssessmentPeriodId)
//                    ->where('teacher_id', '=', $uniqueTeacherId)->get();
//
//                $groupsAmountFromServiceAreaCode = count($groupsFromServiceAreaCode);
//                $aggregateTotalStudentsReviewersOnServiceArea = 0;
//                $aggregateTotalStudentsEnrolledOnServiceArea = 0;
//
//                $final_first_aggregate_competence_average = 0;
//                $final_second_aggregate_competence_average = 0;
//                $final_third_aggregate_competence_average = 0;
//                $final_fourth_aggregate_competence_average = 0;
//                $final_fifth_aggregate_competence_average = 0;
//                $final_sixth_aggregate_competence_average = 0;
//
//                foreach ($groupsFromServiceAreaCode as $key => $groupFromServiceAreaCode) {
//
//                    $aggregateTotalStudentsReviewersOnServiceArea += $groupsFromServiceAreaCode[$key]->students_amount_reviewers;
//                    $aggregateTotalStudentsEnrolledOnServiceArea += $groupsFromServiceAreaCode[$key]->students_amount_on_group;
//
//                    $final_first_aggregate_competence_average += $groupFromServiceAreaCode->first_final_competence_average;
//                    $final_second_aggregate_competence_average += $groupFromServiceAreaCode->second_final_competence_average;
//                    $final_third_aggregate_competence_average += $groupFromServiceAreaCode->third_final_competence_average;
//                    $final_fourth_aggregate_competence_average += $groupFromServiceAreaCode->fourth_final_competence_average;
//                    $final_fifth_aggregate_competence_average += $groupFromServiceAreaCode->fifth_final_competence_average;
//                    $final_sixth_aggregate_competence_average += $groupFromServiceAreaCode->sixth_final_competence_average;
//                }
//
//                $final_first_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
//                $final_second_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
//                $final_third_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
//                $final_fourth_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
//                $final_fifth_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
//                $final_sixth_aggregate_competence_average /= $groupsAmountFromServiceAreaCode;
//
//                $final_first_aggregate_competence_average = number_format($final_first_aggregate_competence_average, 1);
//                $final_second_aggregate_competence_average = number_format($final_second_aggregate_competence_average, 1);
//                $final_third_aggregate_competence_average = number_format($final_third_aggregate_competence_average, 1);
//                $final_fourth_aggregate_competence_average = number_format($final_fourth_aggregate_competence_average, 1);
//                $final_fifth_aggregate_competence_average = number_format($final_fifth_aggregate_competence_average, 1);
//                $final_sixth_aggregate_competence_average = number_format($final_sixth_aggregate_competence_average, 1);
//
//                DB::table('teachers_service_areas_results')->updateOrInsert(['teacher_id' => $uniqueTeacherId, 'service_area_code' => $uniqueServiceAreaCode, 'assessment_period_id' => $activeAssessmentPeriodId],
//                    ['first_final_aggregate_competence_average' => $final_first_aggregate_competence_average,
//                        'second_final_aggregate_competence_average' => $final_second_aggregate_competence_average,
//                        'third_final_aggregate_competence_average' => $final_third_aggregate_competence_average,
//                        'fourth_final_aggregate_competence_average' => $final_fourth_aggregate_competence_average,
//                        'fifth_final_aggregate_competence_average' => $final_fifth_aggregate_competence_average,
//                        'sixth_final_aggregate_competence_average' => $final_sixth_aggregate_competence_average,
//                        'aggregate_students_amount_reviewers' => $aggregateTotalStudentsReviewersOnServiceArea,
//                        'aggregate_students_amount_on_service_area' => $aggregateTotalStudentsEnrolledOnServiceArea,
//                        'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
//                        'updated_at' => Carbon::now('GMT-5')->toDateTimeString()]);
//            }
//
//        }
//        $teacherRoleId = Role::getTeacherRoleId();
//        $teachersFrom360 = DB::table('teachers_students_perspectives as tsp')->select(['teacher_id'])
//            ->join('v2_unit_user', 'tsp.teacher_id', '=', 'v2_unit_user.user_id')
//            ->where('v2_unit_user.role_id', '=', $teacherRoleId)->where('assessment_period_id', '=', $activeAssessmentPeriodId)
//            ->get()->toArray();
//
//        $uniqueTeachers = array_column($teachersFrom360, 'teacher_id');
//        $uniqueTeachersId = array_unique($uniqueTeachers);
//
//        foreach ($uniqueTeachersId as $uniqueTeacherId) {
//
//            $allAssessments = [];
//            DB::table('assessment_weights')->get();
//
//            $peerPercentage = 0.15;
//            $autoPercentage = 0.15;
//            $bossPercentage = 0.35;
//            $studentsPercentage = 0.35;
//
//            $firstCompetenceTotal = 0;
//            $secondCompetenceTotal = 0;
//            $thirdCompetenceTotal = 0;
//            $fourthCompetenceTotal = 0;
//            $fifthCompetenceTotal = 0;
//            $sixthCompetenceTotal = 0;
//
//            $peerBossAutoAssessmentAnswers = DB::table('form_answers as fa')
//                ->select(['u.name', 'f.unit_role', 'fa.first_competence_average', 'fa.second_competence_average', 'fa.third_competence_average',
//                    'fa.fourth_competence_average', 'fa.fifth_competence_average', 'fa.sixth_competence_average', 'u.id as teacherId', 'v2_unit_user.unit_identifier',
//                    'v2_units.name as unitName', 'fa.submitted_at'])
//                ->join('forms as f', 'f.id', '=', 'fa.form_id')
//                ->join('users as u', 'u.id', '=', 'fa.teacher_id')
//                ->join('teachers_students_perspectives as tsp', 'tsp.teacher_id', '=', 'u.id')
//                ->join('v2_unit_user', 'v2_unit_user.user_id', '=', 'tsp.teacher_id')
//                ->join('v2_units', 'v2_unit_user.unit_identifier', '=', 'v2_units.identifier')
//                ->where('f.assessment_period_id', '=', $activeAssessmentPeriodId)->where('f.type', '=', 'otros')
//                ->where('u.id', '=', $uniqueTeacherId)
//                ->where('tsp.assessment_period_id', '=', $activeAssessmentPeriodId)
//                ->where('v2_unit_user.role_id', '=', $teacherRoleId)
//                ->where('v2_units.assessment_period_id', '=', $activeAssessmentPeriodId)
//                ->where('fa.assessment_period_id', '=', $activeAssessmentPeriodId)->get();
//
//
////            if($uniqueTeacherId === 144){
////                dd($peerBossAutoAssessmentAnswers);
////            }
//
//            if (count($peerBossAutoAssessmentAnswers) == 0) {
//                continue;
//            }
//
//            $studentsAnswers = DB::table('teachers_students_perspectives as tsp')
//                ->select(['tsp.first_final_aggregate_competence_average as first_competence_average',
//                    'tsp.second_final_aggregate_competence_average as second_competence_average',
//                    'tsp.third_final_aggregate_competence_average as third_competence_average',
//                    'tsp.fourth_final_aggregate_competence_average as fourth_competence_average',
//                    'tsp.fifth_final_aggregate_competence_average as fifth_competence_average',
//                    'tsp.sixth_final_aggregate_competence_average as sixth_competence_average',
//                    'aggregate_students_amount_reviewers', 'aggregate_students_amount_on_360_groups'])
//                ->where('teacher_id', '=', $uniqueTeacherId)->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get()->first();
//
//            $studentsAnswers->unit_role = "estudiante";
//            $peerBossAutoAssessmentAnswers [] = $studentsAnswers;
//            $allAssessments = $peerBossAutoAssessmentAnswers;
//
//            foreach ($allAssessments as $assessment) {
//
//                if ($assessment->unit_role === "par") {
//                    $firstCompetenceTotal += $assessment->first_competence_average * $peerPercentage;
//                    $secondCompetenceTotal += $assessment->second_competence_average * $peerPercentage;
//                    $thirdCompetenceTotal += $assessment->third_competence_average * $peerPercentage;
//                    $fourthCompetenceTotal += $assessment->fourth_competence_average * $peerPercentage;
//                    $fifthCompetenceTotal += $assessment->fifth_competence_average * $peerPercentage;
//                    $sixthCompetenceTotal += $assessment->sixth_competence_average * $peerPercentage;
//                }
//
//                if ($assessment->unit_role === "jefe") {
//                    $firstCompetenceTotal += $assessment->first_competence_average * $bossPercentage;
//                    $secondCompetenceTotal += $assessment->second_competence_average * $bossPercentage;
//                    $thirdCompetenceTotal += $assessment->third_competence_average * $bossPercentage;
//                    $fourthCompetenceTotal += $assessment->fourth_competence_average * $bossPercentage;
//                    $fifthCompetenceTotal += $assessment->fifth_competence_average * $bossPercentage;
//                    $sixthCompetenceTotal += $assessment->sixth_competence_average * $bossPercentage;
//                }
//
//                if ($assessment->unit_role === "estudiante") {
//
//                    $firstCompetenceTotal += $assessment->first_competence_average * $studentsPercentage;
//                    $secondCompetenceTotal += $assessment->second_competence_average * $studentsPercentage;
//                    $thirdCompetenceTotal += $assessment->third_competence_average * $studentsPercentage;
//                    $fourthCompetenceTotal += $assessment->fourth_competence_average * $studentsPercentage;
//                    $fifthCompetenceTotal += $assessment->fifth_competence_average * $studentsPercentage;
//                    $sixthCompetenceTotal += $assessment->sixth_competence_average * $studentsPercentage;
//
//                    $involvedActors = $assessment->aggregate_students_amount_reviewers + count($allAssessments) - 1;
//                    $totalActors = $assessment->aggregate_students_amount_on_360_groups + count($allAssessments) - 1;
//
//                }
//
//                if ($assessment->unit_role === "autoevaluación") {
//
//                    $firstCompetenceTotal += $assessment->first_competence_average * $autoPercentage;
//                    $secondCompetenceTotal += $assessment->second_competence_average * $autoPercentage;
//                    $thirdCompetenceTotal += $assessment->third_competence_average * $autoPercentage;
//                    $fourthCompetenceTotal += $assessment->fourth_competence_average * $autoPercentage;
//                    $fifthCompetenceTotal += $assessment->fifth_competence_average * $autoPercentage;
//                    $sixthCompetenceTotal += $assessment->sixth_competence_average * $autoPercentage;
//                }
//
//            }
//
//
//            $firstCompetenceTotal = number_format($firstCompetenceTotal, 1);
//            $secondCompetenceTotal = number_format($secondCompetenceTotal, 1);
//            $thirdCompetenceTotal = number_format($thirdCompetenceTotal, 1);
//            $fourthCompetenceTotal = number_format($fourthCompetenceTotal, 1);
//            $fifthCompetenceTotal = number_format($fifthCompetenceTotal, 1);
//            $sixthCompetenceTotal = number_format($sixthCompetenceTotal, 1);
//
//            DB::table('teachers_360_final_average')->updateOrInsert(
//                ['teacher_id' => $uniqueTeacherId, 'assessment_period_id' => $activeAssessmentPeriodId],
//                ['first_final_aggregate_competence_average' => $firstCompetenceTotal,
//                    'second_final_aggregate_competence_average' => $secondCompetenceTotal,
//                    'third_final_aggregate_competence_average' => $thirdCompetenceTotal,
//                    'fourth_final_aggregate_competence_average' => $fourthCompetenceTotal,
//                    'fifth_final_aggregate_competence_average' => $fifthCompetenceTotal,
//                    'sixth_final_aggregate_competence_average' => $sixthCompetenceTotal,
//                    'involved_actors' => $involvedActors,
//                    'total_actors' => $totalActors]);
//
//            /*       dd($firstCompetenceTotal,$secondCompetenceTotal,$thirdCompetenceTotal,$fourthCompetenceTotal,$fifthCompetenceTotal,$sixthCompetenceTotal);*/
//
//            /*        dd($allAssessments);*/
//        }
//    }
//
//    dd("listo");
//
//});


Route::get('/aggregateReport', function () {
    $tableData = [];

    $groupResults = DB::table('group_results as gr')
        ->select([
            'g.name as group_name', 'g.class_code', 'g.group as group_number', 'ap.name as assessment_period_name',
            'ap.id as assessment_period_id',
            'u.name as teacher_name', 'gr.students_amount_reviewers', 'gr.students_amount_on_group',
            'gr.first_final_competence_average as first_competence',
            'gr.second_final_competence_average as second_competence',
            'gr.third_final_competence_average as third_competence',
            'gr.fourth_final_competence_average as fourth_competence',
            'gr.fifth_final_competence_average as fifth_competence',
            'gr.sixth_final_competence_average as sixth_competence',
            'sa.name as service_area_name', 'sa.code as service_area_code'
        ])
        ->join('groups as g', 'gr.group_id', '=', 'g.group_id')
        ->join('assessment_periods as ap', 'gr.assessment_period_id', '=', 'ap.id')
        ->join('users as u', 'gr.teacher_id', '=', 'u.id')
        ->join('service_areas as sa', function ($join) {
            $join->on('gr.service_area_code', '=', 'sa.code')
                ->on('gr.assessment_period_id', '=', 'sa.assessment_period_id'); // Additional condition
        })->orderBy('u.name', 'ASC')->get();


    foreach ($groupResults as $groupResult) {

        $rowData = [];

        if ($groupResult->assessment_period_id === 5 && !str_starts_with(trim($groupResult->service_area_code), 'I')) {
            //Remember that, unless the service area code starts with I, the final average will be the same first_final_competence_average
            $groupResult->final_average = $groupResult->first_competence;
        } else {
            $groupResult->final_average = ($groupResult->first_competence + $groupResult->second_competence
                    + $groupResult->third_competence + $groupResult->fourth_competence + $groupResult->fifth_competence +
                    $groupResult->sixth_competence) / 6;
        }

        $rowData [] = $groupResult->assessment_period_name;
        $rowData [] = $groupResult->group_name;
        $rowData [] = $groupResult->class_code;
        $rowData [] = $groupResult->group_number;
        $rowData [] = $groupResult->teacher_name;
        $rowData [] = $groupResult->service_area_name;
        $rowData [] = $groupResult->students_amount_on_group;
        $rowData [] = $groupResult->students_amount_reviewers;
        $rowData [] = $groupResult->final_average;

        $tableData [] = $rowData;
    }

    return Excel::download(new \App\Exports\AllPeriodsReportPerGroupViewExport($tableData), 'Resultados_Reporte_General_Grupo.xlsx');

});


Route::get('individualExcelReport', function () {

    $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

    $form = DB::table('forms')->where('id', '=', 39)->first();

    $formQuestions = \App\Models\Form::getFormQuestions($form);

    $formAnswers = DB::table('form_answers as fa')->select(['u.name as teacher_name', 'g.name as group_name', 'g.group as group_number', 'fa.answers'])
        ->where('fa.assessment_period_id', '=', $activeAssessmentPeriodId)
        ->where('fa.group_id', '!=', null)
        ->where('fa.form_id', '=', 39)->join('users as u', 'fa.teacher_id', '=', 'u.id')
        ->join('groups as g', 'fa.group_id', '=', 'g.group_id')->orderBy('u.name', 'ASC')
        ->orderBy('g.name', 'ASC')->orderBy('g.group', 'ASC')->get();

    $tableData = [];

    foreach ($formAnswers as $formAnswer) {

        //Every row must have the formAnswer's teacher's name, name and number of the group, all the answers value with the question name and the comments
        $rowData = [];

        $rowData [] = $formAnswer->teacher_name;
        $rowData [] = $formAnswer->group_name;
        $rowData [] = (int)$formAnswer->group_number;


        $formAnswerAsJson = (json_decode($formAnswer->answers, false, 512, JSON_THROW_ON_ERROR));
        //Now we insert the results for every question in case the question exists in the form_answer

        foreach ($formQuestions as $question) {
            $result = Form::findFirstOccurrence($formAnswerAsJson, $question);
            if ($result) {
                $rowData[] = $result->answer;
                continue;
            }
            $rowData [] = "";
        }
        $tableData[] = $rowData;

    }

    return Excel::download(new \App\Exports\IndividualCompetenceResultsViewExport($formQuestions, $tableData), 'Resultados_2024A.xlsx');

});


<?php

use App\Exports\ResultsViewExport;
use App\Helpers\AtlanteProvider;
use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use App\Models\Enroll;

use Revolution\Google\Sheets\Facades\Sheets;


use App\Models\Form;
use App\Models\Group;
use App\Models\Reports;
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

/* >>>>>>>>>>>>>>>>>>>>>>>>>>>> External routes <<<<<<<<<<<<<<<<<<<<<<<< */
Route::get('/evaluacionDocente', [\App\Http\Controllers\AssessmentController::class, 'hasStudentFinishedAssessment'])->name('redirect');

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
Route::get('/assessmentPeriods/legacy', [\App\Http\Controllers\AssessmentPeriodController::class, 'getLegacyAssessmentPeriods'])->middleware(['auth'])->name('api.assessmentPeriods.legacy');
Route::get('/assessmentPeriods/notLegacy', [\App\Http\Controllers\AssessmentPeriodController::class, 'getNotLegacyAssessmentPeriods'])->middleware(['auth'])->name('api.assessmentPeriods.notLegacy');


/* >>>>>>>>>>>>>>>>>>>>>>>  Assessment Ponderations >>>>>>>><<<<<< */
Route::inertia('/assessmentWeights', 'AssessmentWeights/Index')->middleware(['auth', 'isAdmin'])->name('assessmentWeights.index.view');
Route::resource('api/assessmentWeights', \App\Http\Controllers\AssessmentWeightController::class, [
    'as' => 'api'
])->middleware('auth');


Route::resource('api/competences', \App\Http\Controllers\CompetenceController::class, [
    'as' => 'api'
])->middleware('auth');


/* >>>>>>>>>>>>>>>>>>>>> Faculties routes <<<<<<<<<<<<<<<<<<<< */
Route::resource('api/faculties', \App\Http\Controllers\FacultyController::class, [
    'as' => 'api'
])->middleware('auth');
Route::inertia('/faculties', 'Faculties/Index')->middleware(['auth', 'isAdmin'])->name('faculties.index.view');


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
Route::post('/api/serviceAreas/user/assign', [\App\Http\Controllers\ServiceAreaController::class, 'assignServiceAreas'])->middleware(['auth'])->name('api.serviceAreas.assign');
Route::post
('/api/serviceAreas/user/delete', [\App\Http\Controllers\ServiceAreaController::class, 'deleteServiceAreasFromUser'])->middleware(['auth'])->name('api.serviceAreas.deleteAssignments');
Route::get('serviceAreas/getServiceAreasResults', [\App\Http\Controllers\ServiceAreaController::class, 'getServiceAreasResults'])->middleware(['auth'])->name('serviceAreas.getResults');
Route::get('serviceAreas/getServiceAreasResultsPerGroup', [\App\Http\Controllers\ServiceAreaController::class, 'getServiceAreasResultsPerGroup'])->middleware(['auth'])->name('serviceAreas.getResultsPerGroup');
Route::get('serviceAreas/getTeachersWithResults', [\App\Http\Controllers\ServiceAreaController::class, 'getServiceAreasTeachersWithResults'])
    ->middleware(['auth'])->name('serviceAreas.teachersWithResults');
Route::get('serviceAreas/admins', [\App\Http\Controllers\ServiceAreaController::class, 'getServiceAreasAdmins'])->name('serviceAreas.admins')->middleware(['auth']);
Route::get('/serviceAreas/{serviceAreaCode}', [\App\Http\Controllers\ServiceAreaController::class, 'edit'])->middleware(['auth', 'isAdminOrUnitAdmin'])->name('serviceAreas.manageServiceArea');
Route::get('serviceArea/{serviceAreaCode}/admins', [\App\Http\Controllers\ServiceAreaController::class, 'getServiceAreaAdmins'])->name('serviceArea.admins')->middleware(['auth']);
Route::post('serviceArea/assignAdmin', [\App\Http\Controllers\ServiceAreaController::class, 'assignServiceAreaAdmin'])->name('serviceArea.assignAdmin')->middleware(['auth']);
Route::post('serviceArea/deleteAdmin', [\App\Http\Controllers\ServiceAreaController::class, 'deleteServiceAreaAdmin'])->name('serviceArea.deleteAdmin')->middleware(['auth']);
Route::inertia('/serviceArea/admins/view', 'ServiceAreas/ManageAdmins')->middleware(['auth', 'isAdmin'])->name('serviceAreas.manage.admins');


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
//Route::get('groups/purify', [\App\Http\Controllers\GroupController::class, 'purify'])->middleware(['auth'])->name('groups.purify');


/* >>>>>>>>>>>>>>>>>>>>>>> Enrolls routes <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::inertia('/enrolls', 'Enrolls/Index')->middleware(['auth', 'isAdmin'])->name('enrolls.index.view');
//Create fake unites
Route::get('/api/enrolls/sync', [\App\Http\Controllers\EnrollController::class, 'sync'])->middleware(['auth']);
Route::resource('api/enrolls', \App\Http\Controllers\EnrollController::class, [
    'as' => 'api'
])->middleware('auth');
//Sync groups
Route::post('/api/enrolls/sync', [\App\Http\Controllers\EnrollController::class, 'sync'])->middleware(['auth'])->name('api.enrolls.sync');

/* >>>>>>>>>>>>>>>>>>>>>>>>>>>> Reports routes <<<<<<<<<<<<<<<<<<<<<<<< */

Route::post('/reports/pdf/serviceArea', [\App\Http\Controllers\ReportsController::class, 'downloadTeachingPDF'])->middleware(['auth'])->name('reports.teaching.download');

Route::post('/reports/pdf/faculty', [\App\Http\Controllers\ReportsController::class, 'downloadFacultyPDF'])->middleware(['auth'])->name('reports.faculty.download');


Route::get('/reports/index', [\App\Http\Controllers\ReportsController::class, 'show360Assessment'])->middleware(['auth'])->name('reports.show360Assessment');
Route::post('/reports/results/group', [\App\Http\Controllers\ReportsController::class, 'getGroupResults'])->middleware(['auth'])->name('reports.group.results');
Route::post('/reports/results/serviceArea', [\App\Http\Controllers\ReportsController::class, 'getServiceAreaResults'])->middleware(['auth'])->name('reports.serviceArea.results');
Route::post('/reports/results/finalTeaching', [\App\Http\Controllers\ReportsController::class, 'getFinalTeachingResults'])->middleware(['auth'])->name('reports.finalTeaching.results');
Route::post('/reports/results/faculties', [\App\Http\Controllers\ReportsController::class, 'getFacultyResults'])->middleware(['auth'])->name('reports.faculty.results');


Route::get('/reports/show360Assessment', [\App\Http\Controllers\ReportsController::class, 'show360Assessment'])->middleware(['auth'])->name('reports.show360Assessment');
Route::get('/reports/showServiceAreasAssessment', [\App\Http\Controllers\ReportsController::class, 'showServiceAreasAssessment'])->middleware(['auth'])->name('reports.showServiceAreasAssessment');

Route::get('/reports/serviceArea', [\App\Http\Controllers\ReportsController::class, 'indexServiceAreaResults'])->middleware(['auth'])->name('reports.serviceArea');
Route::inertia('/reports/overallTeaching', 'Reports/OverallTeaching')->middleware(['auth', 'isAdmin'])->name('reports.overallTeaching');

Route::get('/reports/legacyReports', [\App\Http\Controllers\ReportsController::class, 'indexLegacyGroupResults'])->middleware(['auth'])->name('reports.legacy.view');

Route::inertia('/reports/faculties', 'Reports/Faculties')->middleware(['auth', 'isAdmin'])->name('reports.faculties.view');


Route::get('/reports/showServiceArea', [\App\Http\Controllers\ReportsController::class, 'showServiceArea'])->middleware(['auth'])->name('reports.showServiceArea');
Route::get('/reports/showServiceAreaGroup', [\App\Http\Controllers\ReportsController::class, 'showServiceAreaGroup'])->middleware(['auth'])->name('reports.showServiceAreaGroup');
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



Route::get('/insertConsultorioJuridicoGroupResults', function () {

    $groups = AtlanteProvider::get('groups', [
        'periods' => '2024B',
    ], true);

    $enrolls = AtlanteProvider::get('enrolls', [
        'periods' => '2024B'
    ], true);

    $consultorioCourses = [
        [
            'class_code' => '51A41',
            'teachers' => ['adriana.covaleda@unibague.edu.co', 'fredy.camacho@unibague.edu.co',
                'sandra.obando@unibague.edu.co', 'sandra.munoz@unibague.edu.co', 'mauricio.gomez@unibague.edu.co',
                'santiago.sanchez@unibague.edu.co']
        ],
        [
            'class_code' => '51A47',
            'teachers' => ['adriana.covaleda@unibague.edu.co', 'fredy.camacho@unibague.edu.co',
                'sandra.obando@unibague.edu.co', 'sandra.munoz@unibague.edu.co', 'mauricio.gomez@unibague.edu.co',
                'santiago.sanchez@unibague.edu.co']
        ],

        [
            'class_code' => '51A49',
            'teachers' => ['adriana.covaleda@unibague.edu.co', 'fredy.camacho@unibague.edu.co',
                'mauricio.gomez@unibague.edu.co',
                'sandra.obando@unibague.edu.co', 'sandra.munoz@unibague.edu.co','monica.cardenas@unibague.edu.co',
                'santiago.sanchez@unibague.edu.co']
        ]
    ];

    foreach ($consultorioCourses as $consultorioCourse) {

        $consultorioGroupsFromSIGA = array_filter($groups, function ($group) use ($consultorioCourse) {
            return $group['teacher_email'] !== "" && $group['degree_code'] !== "" && Group::isConsultorioJuridico($group['name']) && $group['class_code'] === $consultorioCourse["class_code"];
        });

        Group::createConsultorioGroups($consultorioGroupsFromSIGA, $consultorioCourse['teachers']);

        Group::upsertConsultorioGroupResults($consultorioCourse['class_code'], $consultorioGroupsFromSIGA, $consultorioCourse['teachers'], $enrolls);
    }
});


Route::get('/unlockStudentsWithNonSuitableGroups', function () {

    set_time_limit(50000);

    $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
    $academicPeriods = AcademicPeriod::getCurrentAcademicPeriods();

    foreach ($academicPeriods as $academicPeriod) {

        $groups = AtlanteProvider::get('groups', [
            'periods' => $academicPeriod->name,
        ], true);

        $nonSuitableGroups = array_filter($groups, function ($group) {
            return $group['teacher_email'] !== "" && $group['degree_code'] !== "" && !Group::isSuitableGroup($group['name']);
        });

        if (count($nonSuitableGroups) === 0) {
            continue;
        }

        $groupIds = array_column($nonSuitableGroups, 'group_id');

        $students = AtlanteProvider::get('enrolls', [
            'periods' => $academicPeriod->name
        ], true);

        $studentsToUnlock = [];
        foreach ($students as $student) {
            $studentGroupIds = explode(',', $student['group_id']);
            if (count(array_intersect($studentGroupIds, $groupIds)) > 0) {
                $studentsToUnlock[] = $student;
            }
        }

        //Now, for every student, check if the student has no other records in the group_user table.
        //If that's the case, then the user is suitable to be sent to unlock

        $studentEmails = array_unique(array_column($studentsToUnlock, 'email'));

        $realStudentsToUnlock = [];

        foreach ($studentEmails as $studentEmail) {

            $userName = explode('@', $studentEmail)[0];
            $user = DB::table('users')->where('email', '=', $studentEmail)->first();

            if (!$user) {
                $nonExistingStudentsInAletheia [] = $userName;
            } else {
                $doesStudentHaveOtherGroups = DB::table('group_user')->where('academic_period_id', '=', $academicPeriod->id)
                    ->where('user_id', '=', $user->id)->get();

                if ($doesStudentHaveOtherGroups->count() > 0) {
                    $realStudentsToUnlock [] = ['id' => $user->id, 'name' => $userName];
                }
            }
        }

        foreach ($nonExistingStudentsInAletheia as $nonExistingStudent) {
            AtlanteProvider::get('grades/enable', [
                'academic_period' => $academicPeriod->name,
                'user_name' => $nonExistingStudent
            ]);
        }
        foreach ($realStudentsToUnlock as $student) {
            $response = AtlanteProvider::get('grades/enable', [
                'academic_period' => $academicPeriod->name,
                'user_name' => $student['name']
            ])[0];
            $now = \Illuminate\Support\Carbon::now()->toDateTimeString();
            DB::table('students_completed_assessment_audit')->updateOrInsert(['user_id' => $student['id'],
                'academic_period_id' => $academicPeriod->id],
                ['assessment_period_id' => $activeAssessmentPeriodId, 'message' => $response->status,
                    'created_at' => $now, 'updated_at' => $now]);

        }
    }
});


Route::get('/updateServiceAreasResultsTable', function () {

    $activeAssessmentPeriodId = \App\Models\AssessmentPeriod::getActiveAssessmentPeriod()->id;

    // Retrieve unique service area codes for the active assessment period
    $serviceAreaCodes = DB::table('teachers_service_areas_results as tsar')
        ->select('tsar.service_area_code')
        ->where('tsar.assessment_period_id', '=', $activeAssessmentPeriodId)
        ->pluck('tsar.service_area_code')
        ->unique();

    // Define hour types to loop through
    $hourTypes = ['normal', 'cátedra', 'total'];

    // Loop through each service area code and hour type
    foreach ($serviceAreaCodes as $serviceAreaCode) {
        foreach ($hourTypes as $hourType) {
            $studentsWithAnswer = 0;
            $studentsEnrolled = 0;
            $competencesData = [];
            $overallAverage = 0;
            $openEndedAnswers = [];

            // Get all results for the current service area code and hour type
            $results = DB::table('teachers_service_areas_results as tsar')
                ->where('tsar.service_area_code', '=', $serviceAreaCode)
                ->where('tsar.assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('tsar.hour_type', '=', $hourType)
                ->join('users', 'tsar.teacher_id', '=', 'users.id')
                ->get();

            $resultsCount = $results->count();

            foreach ($results as $result) {
                $openEndedAnswers[] = [
                    'answers' => json_decode($result->open_ended_answers, true),
                    'teacher_name' => $result->name,
                ];

                $competencesAverage = json_decode($result->competences_average, true);
                $overallAverage += $result->overall_average;
                $studentsWithAnswer += $result->aggregate_students_amount_reviewers;
                $studentsEnrolled += $result->aggregate_students_amount_on_service_area;

                foreach ($competencesAverage as $competence) {
                    $competenceId = $competence['id'];
                    if (!isset($competencesData[$competenceId])) {
                        $competencesData[$competenceId] = [
                            'id' => $competenceId,
                            'name' => $competence['name'],
                            'attributes' => [],
                            'overall_sum' => 0,
                            'overall_count' => 0,
                        ];
                    }

                    $competencesData[$competenceId]['overall_sum'] += $competence['overall_average'];
                    $competencesData[$competenceId]['overall_count']++;

                    foreach ($competence['attributes'] as $attributeValue) {
                        $attributeName = $attributeValue['name'];
                        if (!isset($competencesData[$competenceId]['attributes'][$attributeName])) {
                            $competencesData[$competenceId]['attributes'][$attributeName] = [
                                'name' => $attributeName,
                                'sum' => 0,
                                'count' => 0,
                            ];
                        }
                        if (isset($attributeValue['overall_average'])) {
                            $competencesData[$competenceId]['attributes'][$attributeName]['sum'] += $attributeValue['overall_average'];
                            $competencesData[$competenceId]['attributes'][$attributeName]['count']++;
                        }
                    }
                }
            }

            // Calculate the final averages for competences
            $finalCompetencesData = [];
            $overallAverage = $resultsCount > 0 ? $overallAverage / $resultsCount : 0;

            foreach ($competencesData as $competence) {
                $finalCompetence = [
                    'id' => $competence['id'],
                    'name' => $competence['name'],
                    'attributes' => [],
                    'overall_average' => round($competence['overall_sum'] / $competence['overall_count'], 2),
                ];

                foreach ($competence['attributes'] as $attributeName => $attributeData) {
                    if ($attributeData['count'] > 0) {
                        $finalCompetence['attributes'][] = [
                            'name' => $attributeName,
                            'overall_average' => round($attributeData['sum'] / $attributeData['count'], 2),
                        ];
                    }
                }
                $finalCompetencesData[] = $finalCompetence;
            }

            // Insert or update the service area result
            if ($resultsCount > 0) {
                DB::table('service_area_results')->updateOrInsert(
                    [
                        'service_area_code' => $serviceAreaCode,
                        'assessment_period_id' => $activeAssessmentPeriodId,
                        'hour_type' => $hourType,
                    ],
                    [
                        'competences_average' => json_encode($finalCompetencesData, JSON_UNESCAPED_UNICODE),
                        'overall_average' => round($overallAverage, 2),
                        'open_ended_answers' => json_encode($openEndedAnswers, JSON_UNESCAPED_UNICODE),
                        'students_reviewers' => $studentsWithAnswer,
                        'students_enrolled' => $studentsEnrolled,
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString()
                    ]
                );
            }
        }
    }
});


Route::get('/updateFacultiesResultsTable', function () {


    // Obtain the active assessment period ID
    $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

    // Retrieve all faculties with their service areas for the active assessment period
    $faculties = \App\Models\Faculty::with('serviceAreas')->get();

    // Define the hour types to iterate over
    $hourTypes = ['normal', 'cátedra', 'total'];

    // Loop through each faculty
    foreach ($faculties as $faculty) {
        foreach ($hourTypes as $hourType) {
            $studentsWithAnswer = 0;
            $studentsEnrolled = 0;
            $competencesData = [];
            $overallAverage = 0;

            // Loop through each service area in the faculty
            foreach ($faculty->serviceAreas as $serviceArea) {

                // Retrieve service area results for the specific hour type
                $serviceAreaResults = DB::table('service_area_results')
                    ->where('service_area_code', $serviceArea->code)
                    ->where('assessment_period_id', $activeAssessmentPeriodId)
                    ->where('hour_type', $hourType)
                    ->get();

                // If there are no results, skip this iteration
                if ($serviceAreaResults->isEmpty()) {
                    continue;
                }

                // Aggregate the results
                foreach ($serviceAreaResults as $result) {
                    $overallAverage += $result->overall_average;
                    $studentsWithAnswer += $result->students_reviewers;
                    $studentsEnrolled += $result->students_enrolled;

                    // Decode the competences average data
                    $competencesAverage = json_decode($result->competences_average, true);
                    foreach ($competencesAverage as $competence) {
                        $competenceId = $competence['id'];

                        // Initialize competence data if not already set
                        if (!isset($competencesData[$competenceId])) {
                            $competencesData[$competenceId] = [
                                'id' => $competenceId,
                                'name' => $competence['name'],
                                'overall_sum' => 0,
                                'overall_count' => 0,
                                'attributes' => []
                            ];
                        }

                        // Aggregate overall competence averages
                        $competencesData[$competenceId]['overall_sum'] += $competence['overall_average'];
                        $competencesData[$competenceId]['overall_count']++;

                        // Loop through each attribute in the competence
                        foreach ($competence['attributes'] as $attribute) {
                            $attributeName = $attribute['name'];

                            // Initialize attribute data if not already set
                            if (!isset($competencesData[$competenceId]['attributes'][$attributeName])) {
                                $competencesData[$competenceId]['attributes'][$attributeName] = [
                                    'name' => $attributeName,
                                    'sum' => 0,
                                    'count' => 0
                                ];
                            }

                            // Aggregate attribute average
                            if (isset($attribute['overall_average'])) {
                                $competencesData[$competenceId]['attributes'][$attributeName]['sum'] += $attribute['overall_average'];
                                $competencesData[$competenceId]['attributes'][$attributeName]['count']++;
                            }
                        }
                    }
                }
            }

            // Calculate final averages for each competence and its attributes
            $finalCompetencesData = [];
            $overallAverage = $faculty->serviceAreas->count() > 0 ? $overallAverage / $faculty->serviceAreas->count() : 0;

            foreach ($competencesData as $competence) {
                $finalCompetence = [
                    'id' => $competence['id'],
                    'name' => $competence['name'],
                    'overall_average' => round($competence['overall_sum'] / $competence['overall_count'], 2),
                    'attributes' => []
                ];

                foreach ($competence['attributes'] as $attributeName => $attributeData) {
                    if ($attributeData['count'] > 0) {
                        $finalCompetence['attributes'][] = [
                            'name' => $attributeName,
                            'overall_average' => round($attributeData['sum'] / $attributeData['count'], 2),
                        ];
                    }
                }
                $finalCompetencesData[] = $finalCompetence;
            }
            if (!empty($finalCompetencesData)) {
                // Insert or update the results in the `faculties_results` table
                DB::table('faculty_results')->updateOrInsert(
                    [
                        'faculty_id' => $faculty->id,
                        'assessment_period_id' => $activeAssessmentPeriodId,
                        'hour_type' => $hourType,
                    ],
                    [
                        'overall_average' => round($overallAverage, 2),
                        'students_enrolled' => $studentsEnrolled,
                        'students_reviewers' => $studentsWithAnswer,
                        'competences_average' => json_encode($finalCompetencesData, JSON_UNESCAPED_UNICODE),
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString()
                    ]
                );
            }

        }
    }
});

Route::get('/jejeje', function () {

    set_time_limit(500000);

    $students = DB::table('group_user as gu')
        ->selectRaw('
        gu.user_id,
        u.name,
        u.email,
        (
            SELECT MAX(fa.submitted_at)
            FROM form_answers fa
            WHERE fa.user_id = gu.user_id
            AND fa.assessment_period_id = 6
        ) AS latest_submitted_at
    ')
        ->join('users as u', 'u.id', '=', 'gu.user_id')
        ->where('gu.academic_period_id', 35)
        ->whereIn('gu.user_id', function ($query) {
            $query->select('user_id')
                ->from('group_user')
                ->where('academic_period_id', 35)
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) = SUM(CASE WHEN has_answer = 1 THEN 1 ELSE 0 END)');
        })
        ->groupBy('gu.user_id', 'u.name')
        ->orderBy('latest_submitted_at', 'ASC')
        ->get()->map(function ($item) {
            return [
                'user_id' => $item->user_id,
                'email' => $item->email,
                'academic_period_id' => 35,
                'assessment_period_id' => 6,
                'created_at' => $item->latest_submitted_at,
                'updated_at' => $item->latest_submitted_at,
            ];
        })->toArray();


    foreach ($students as $student) {

        $response = AtlanteProvider::get('grades/enable', [
            'academic_period' => '2024B',
            'user_name' => explode("@", $student['email'])[0]
        ])[0];


        DB::table('students_completed_assessment_audit')->updateOrInsert(['user_id' => $student['user_id'],
            'academic_period_id' => 35],
            ['assessment_period_id' => 6, 'message' => $response->status,
                'created_at' => $student['created_at'], 'updated_at' => $student['updated_at']]);

    }


});



Route::get('/migrateLegacyRecordsFormAnswersTable', function () {
    $formAnswers = DB::table('form_answers as fa')->where('assessment_period_id', '=', 5)
        ->where('competences_average', '=', null)->take(8000)->get();
    foreach ($formAnswers as $formAnswer) {
        $results = \App\Models\FormAnswers::getCompetencesAverage(json_decode($formAnswer->answers, JSON_THROW_ON_ERROR));
        $openEndedAnswers = \App\Models\FormAnswers::getOpenEndedAnswersFromFormAnswer(json_decode($formAnswer->answers, JSON_THROW_ON_ERROR));
        DB::table('form_answers as fa')->
        updateOrInsert(['id' => $formAnswer->id], ['competences_average' => $results['competences']
            , 'overall_average' => $results['overall_average'], 'open_ended_answers' => $openEndedAnswers]);
    }
});

Route::get('/migrateLegacyRecordsGroupResultsTable', function () {

    $legacyCompetences = [
        'C1' => 'Orientación a la calidad educativa',
        'C2' => 'Trabajo Colaborativo',
        'C3' => 'Empatía Universitaria',
        'C4' => 'Comunicación',
        'C5' => 'Innovación del conocimiento',
        'C6' => 'Productividad académica'
    ];

    $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

    //Get the ID's from the teachers that had answers on the active assessment period id
    $teacherIds = DB::table('form_answers as fa')->select(['fa.teacher_id'])->join('forms as f', 'fa.form_id', '=', 'f.id')
        ->where('f.type', '=', 'estudiantes')
        ->where('fa.assessment_period_id', '=', $activeAssessmentPeriodId)->pluck('fa.teacher_id')->unique();


    //First, we insert the student assessments for each teacher on each group on group_results table (updateGroupResultsFromTeacher)
    if (count($teacherIds) > 0) {
        foreach ($teacherIds as $teacherId) {
            $groups = DB::table('groups as g')->where('g.teacher_id', '=', $teacherId)
                ->join('academic_periods as ap', 'g.academic_period_id', '=', 'ap.id')
                ->where('ap.assessment_period_id', '=', $activeAssessmentPeriodId)->get();

            //Now that we have the groups info for the teacher, we can proceed and do the calculations
            foreach ($groups as $group) {

                if ($group->assessment_period_id == null) {
                    continue;
                }

                $openEndedAnswers = [];
                $competencesAverage = [];
                $competencesTotalScore = array_fill_keys(array_keys($legacyCompetences), 0);   //Puntaje acumulado total de la competencia
                $competencesTotalAnswers = array_fill_keys(array_keys($legacyCompetences), 0);    //Número de preguntas por competencia

                $totalStudentsEnrolledOnGroup = DB::table('group_user')
                    ->where('group_id', '=', $group->group_id)
                    ->count();

                $answersFromGroup = DB::table('form_answers as fa')
                    ->join('forms as f', 'fa.form_id', '=', 'f.id')
                    ->join('groups', 'fa.group_id', '=', 'groups.group_id')
                    ->where('f.type', '=', 'estudiantes')
                    ->where('fa.group_id', '=', $group->group_id)->get();

                if ($answersFromGroup->isEmpty()) {
                    continue;
                }

                $studentsAmount = count($answersFromGroup);
                foreach ($answersFromGroup as $answerFromGroup) {

                    $userOpenEndedAnswers = json_decode($answerFromGroup->open_ended_answers);
                    $openEndedAnswers [] = $userOpenEndedAnswers;
                    $answerFromGroup = json_decode($answerFromGroup->answers, true);
                    foreach ($answerFromGroup as $answerFromQuestion) {
                        $competenceKey = $answerFromQuestion['competence'];

                        // Skip if competenceKey is not a string or integer
                        if (!is_string($competenceKey) && !is_int($competenceKey)) {
                            continue;
                        }

                        if (isset($legacyCompetences[$competenceKey])) {
                            $score = floatval($answerFromQuestion['answer']);
                            $competencesTotalScore[$competenceKey] += $score;
                            $competencesTotalAnswers[$competenceKey]++;
                        }
                    }
                }

                $groupedOpenEndedAnswers = \App\Models\FormAnswers::groupOpenEndedAnswersLegacyGroups($openEndedAnswers);

                $overallAverage = 0;
                $competencesPresent = 0;

                foreach ($legacyCompetences as $key => $name) {
                    if ($competencesTotalAnswers[$key] > 0) {
                        $competenceAverage = round($competencesTotalScore[$key] / ($competencesTotalAnswers[$key]), 2);
                        $competencesAverage[] = [
                            'id' => null,
                            'name' => $name,
                            'overall_average' => $competenceAverage
                        ];
                        $overallAverage += $competenceAverage;
                        $competencesPresent++;
                    }
                }

                // Calculate the final overall average
                if ($competencesPresent > 0) {
                    $overallAverage /= $competencesPresent;
                }

                //Identify the hour type

                $teacherProfile = DB::table('v2_teacher_profiles')->where('user_id', '=', $teacherId)->where('assessment_period_id', '=', $group->assessment_period_id)->first();

                if (!$teacherProfile) {
                    continue;
                }

                $hourType = 'normal';

                if ($teacherProfile->employee_type !== 'DTC') {
                    $hourType = 'cátedra';
                }

                DB::table('group_results')->updateOrInsert
                (
                    [
                        'teacher_id' => $teacherId,
                        'group_id' => $group->group_id,
                        'assessment_period_id' => $group->assessment_period_id
                    ],
                    [
                        'hour_type' => $hourType,
                        'service_area_code' => $group->service_area_code,
                        'students_amount_reviewers' => $studentsAmount,
                        'students_amount_on_group' => $totalStudentsEnrolledOnGroup,
                        'competences_average' => json_encode($competencesAverage, JSON_UNESCAPED_UNICODE),
                        'overall_average' => round($overallAverage, 2),
                        'open_ended_answers' => json_encode($groupedOpenEndedAnswers, JSON_UNESCAPED_UNICODE),
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString()
                    ]
                );
            }
        }
    }
});

Route::get('/migrateLegacyRecordsGroupResultsTableeffewwefwe', function () {

    //Get the ID's from the teachers that had answers on the active assessment period id
    $teacherIds = DB::table('form_answers as fa')->select(['fa.teacher_id'])
        ->join('forms as f', 'fa.form_id', '=', 'f.id')
        ->where('f.type', '=', 'estudiantes')
        ->where('fa.assessment_period_id', '=', 6)->pluck('fa.teacher_id')->unique();

    //First, we insert the student assessments for each teacher on each group on group_results table (updateGroupResultsFromTeacher)
    if (count($teacherIds) > 0) {
        foreach ($teacherIds as $teacherId) {
            $groups = DB::table('groups as g')->where('g.teacher_id', '=', $teacherId)
                ->join('academic_periods as ap', 'g.academic_period_id', '=', 'ap.id')
                ->where('ap.assessment_period_id', '=', 6)->get();

            //Now that we have the groups info for the teacher, we can proceed and do the calculations
            foreach ($groups as $group) {

                if ($group->group_id === 13020) {
                    dd($group);
                }

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
                        $competencesData[$competenceKey]['totalAnswers']++;

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

                        if ($competenceName !== 'Satisfacción') {
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
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString()
                    ]
                );
            }
        }
    }
});


Route::get('/migrateLegacyRecordsServiceAreasTable', function () {

    $activeAssessmentPeriodId = \App\Models\AssessmentPeriod::getActiveAssessmentPeriod()->id;
    $teacherIds = DB::table('group_results as gr')
        ->select(['gr.teacher_id'])
        ->where('gr.assessment_period_id', '=', 6)
        ->orderBy('id', 'desc')
        ->pluck('gr.teacher_id')
        ->unique();

    foreach ($teacherIds as $teacherId) {
        $serviceAreasIds = DB::table('group_results as gr')
            ->select(['gr.service_area_code'])
            ->where('teacher_id', '=', $teacherId)
            ->where('gr.assessment_period_id', '=', 6)
            ->pluck('gr.service_area_code')
            ->unique();

        foreach ($serviceAreasIds as $serviceAreaId) {
            $hourTypes = ['normal', 'cátedra'];
            $results = [];
            $allGroups = [];

            foreach ($hourTypes as $hourType) {
                $groups = DB::table('group_results as gr')
                    ->where('gr.teacher_id', '=', $teacherId)
                    ->where('gr.service_area_code', '=', $serviceAreaId)
                    ->where('gr.assessment_period_id', '=', 6)
                    ->where('gr.hour_type', '=', $hourType)
                    ->join('groups as g', 'g.group_id', '=', 'gr.group_id')
                    ->get();

                if ($groups->isNotEmpty()) {
                    $allGroups[$hourType] = $groups;
                }
            }

            // Process each hour type
            foreach ($allGroups as $hourType => $groups) {
                $studentsWithAnswer = 0;
                $studentsEnrolled = 0;
                $openEndedAnswers = [];
                $competencesData = [];
                $overallAverage = 0;

                foreach ($groups as $group) {
                    $competencesAverage = json_decode($group->competences_average, true);
                    $overallAverage += $group->overall_average;

                    foreach ($competencesAverage as $competence) {
                        $competenceId = $competence['id'];
                        if (!isset($competencesData[$competenceId])) {
                            $competencesData[$competenceId] = [
                                'id' => $competenceId,
                                'name' => $competence['name'],
                                'attributes' => [],
                                'overall_sum' => 0,
                                'overall_count' => 0,
                            ];
                        }

                        $competencesData[$competenceId]['overall_sum'] += $competence['overall_average'];
                        $competencesData[$competenceId]['overall_count']++;

                        foreach ($competence['attributes'] as $attributeValue) {
                            $attributeName = $attributeValue['name'];
                            if (!isset($competencesData[$competenceId]['attributes'][$attributeName])) {
                                $competencesData[$competenceId]['attributes'][$attributeName] = [
                                    'name' => $attributeName,
                                    'sum' => 0,
                                    'count' => 0,
                                ];
                            }
                            if (isset($attributeValue['overall_average'])) {
                                $competencesData[$competenceId]['attributes'][$attributeName]['sum'] += $attributeValue['overall_average'];
                                $competencesData[$competenceId]['attributes'][$attributeName]['count']++;
                            }
                        }
                    }

                    $studentsWithAnswer += $group->students_amount_reviewers;
                    $studentsEnrolled += $group->students_amount_on_group;
                    $openEndedAnswers[] = [
                        'group_name' => $group->name . " | Grupo: " . $group->group,
                        'questions' => json_decode($group->open_ended_answers, true),
                    ];
                }

                $groupCount = count($groups);
                $finalCompetencesData = [];
                $overallAverage = $overallAverage / $groupCount;

                foreach ($competencesData as $competence) {
                    $finalCompetence = [
                        'id' => $competence['id'],
                        'name' => $competence['name'],
                        'attributes' => [],
                        'overall_average' => round($competence['overall_sum'] / $competence['overall_count'], 2),
                    ];

                    foreach ($competence['attributes'] as $attributeName => $attributeData) {
                        if ($attributeData['count'] > 0) {
                            $finalCompetence['attributes'][] = [
                                'name' => $attributeName,
                                'overall_average' => round($attributeData['sum'] / $attributeData['count'], 2),
                            ];
                        }
                    }
                    $finalCompetencesData[] = $finalCompetence;
                }

                $results[$hourType] = [
                    'competences_average' => $finalCompetencesData,
                    'open_ended_answers' => $openEndedAnswers,
                    'overall_average' => round($overallAverage, 2),
                    'aggregate_students_amount_reviewers' => $studentsWithAnswer,
                    'aggregate_students_amount_on_service_area' => $studentsEnrolled,
                ];
            }

            // Calculate total (average of all groups regardless of hour type)
            if (!empty($allGroups)) {
                $allGroupsFlat = collect($allGroups)->flatten(1);
                $totalResult = [
                    'competences_average' => [],
                    'open_ended_answers' => [],
                    'overall_average' => 0,
                    'aggregate_students_amount_reviewers' => 0,
                    'aggregate_students_amount_on_service_area' => 0,
                ];

                $totalGroupCount = $allGroupsFlat->count();
                $competencesData = [];

                foreach ($allGroupsFlat as $group) {
                    $totalResult['overall_average'] += $group->overall_average;
                    $totalResult['aggregate_students_amount_reviewers'] += $group->students_amount_reviewers;
                    $totalResult['aggregate_students_amount_on_service_area'] += $group->students_amount_on_group;
                    $totalResult['open_ended_answers'][] = [
                        'group_name' => $group->name . " | Grupo: " . $group->group,
                        'questions' => json_decode($group->open_ended_answers, true),
                    ];

                    $competencesAverage = json_decode($group->competences_average, true);
                    foreach ($competencesAverage as $competence) {
                        $competenceId = $competence['id'];
                        if (!isset($competencesData[$competenceId])) {
                            $competencesData[$competenceId] = [
                                'id' => $competenceId,
                                'name' => $competence['name'],
                                'attributes' => [],
                                'overall_sum' => 0,
                                'overall_count' => 0,
                            ];
                        }

                        $competencesData[$competenceId]['overall_sum'] += $competence['overall_average'];
                        $competencesData[$competenceId]['overall_count']++;

                        foreach ($competence['attributes'] as $attributeValue) {
                            $attributeName = $attributeValue['name'];
                            if (!isset($competencesData[$competenceId]['attributes'][$attributeName])) {
                                $competencesData[$competenceId]['attributes'][$attributeName] = [
                                    'name' => $attributeName,
                                    'sum' => 0,
                                    'count' => 0,
                                ];
                            }
                            if (isset($attributeValue['overall_average'])) {
                                $competencesData[$competenceId]['attributes'][$attributeName]['sum'] += $attributeValue['overall_average'];
                                $competencesData[$competenceId]['attributes'][$attributeName]['count']++;
                            }
                        }
                    }
                }

                // Calculate averages for total
                $totalResult['overall_average'] = round($totalResult['overall_average'] / $totalGroupCount, 2);
                foreach ($competencesData as $competence) {
                    $finalCompetence = [
                        'id' => $competence['id'],
                        'name' => $competence['name'],
                        'attributes' => [],
                        'overall_average' => round($competence['overall_sum'] / $competence['overall_count'], 2),
                    ];

                    foreach ($competence['attributes'] as $attributeName => $attributeData) {
                        if ($attributeData['count'] > 0) {
                            $finalCompetence['attributes'][] = [
                                'name' => $attributeName,
                                'overall_average' => round($attributeData['sum'] / $attributeData['count'], 2),
                            ];
                        }
                    }
                    $totalResult['competences_average'][] = $finalCompetence;
                }

                $results['total'] = $totalResult;
            }

            // Upsert results for each hour type and total
            foreach ($results as $hourType => $result) {
                DB::table('teachers_service_areas_results')->updateOrInsert(
                    [
                        'teacher_id' => $teacherId,
                        'service_area_code' => $serviceAreaId,
                        'assessment_period_id' => $activeAssessmentPeriodId,
                        'hour_type' => $hourType
                    ],
                    [
                        'competences_average' => json_encode($result['competences_average'], JSON_UNESCAPED_UNICODE),
                        'open_ended_answers' => json_encode($result['open_ended_answers'], JSON_UNESCAPED_UNICODE),
                        'overall_average' => $result['overall_average'],
                        'aggregate_students_amount_reviewers' => $result['aggregate_students_amount_reviewers'],
                        'aggregate_students_amount_on_service_area' => $result['aggregate_students_amount_on_service_area'],
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString()
                    ]
                );
            }
        }
    }
});


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

Route::get('/testRelationship', function () {

    $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

    $serviceAreasGrouped = DB::table('service_area_user')
        ->join('service_areas', 'service_area_user.service_area_code', '=', 'service_areas.code')
        ->join('users', 'service_area_user.user_id', '=', 'users.id') // Unir con la tabla de usuarios
        ->select(
            'service_area_user.user_id',
            'users.name as user_name',
            'users.email as user_email',
            'service_area_user.service_area_code',
            'service_areas.name as service_area_name'
        )
        ->where('service_area_user.assessment_period_id', '=', $activeAssessmentPeriodId)
        ->where('service_areas.assessment_period_id', '=', $activeAssessmentPeriodId)
        ->get()
        ->groupBy('user_id')
        ->map(function ($items) {
            // Obtenemos el primer elemento para obtener la información del usuario
            $userInfo = $items->first();
            return [
                'user' => [ // Cambiamos la estructura aquí
                    'id' => $userInfo->user_id,
                    'name' => $userInfo->user_name,
                    'email' => $userInfo->user_email,
                ],
                'service_areas' => $items->map(function ($item) {
                    return [
                        'service_area_code' => $item->service_area_code,
                        'service_area_name' => $item->service_area_name,
                    ];
                })->toArray(),
            ];
        });

// $serviceAreasGrouped contendrá un arreglo de usuarios con su respectiva información y áreas de servicio
    return response()->json($serviceAreasGrouped);
});

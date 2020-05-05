<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

//Clear Cache facade value:
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});

//Reoptimized class loader:
Route::get('/optimize', function() {
    $exitCode = Artisan::call('optimize');
    return '<h1>Reoptimized class loader</h1>';
});

//Route cache:
Route::get('/route-cache', function() {
    $exitCode = Artisan::call('route:cache');
    return '<h1>Routes cached</h1>';
});

//Clear Route cache:
Route::get('/route-clear', function() {
    $exitCode = Artisan::call('route:clear');
    return '<h1>Route cache cleared</h1>';
});

//Clear View cache:
Route::get('/view-clear', function() {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>';
});

//Clear Config cache:
Route::get('/config-cache', function() {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>';
});

//Clear users:
Route::get('/users-clear', function() {
    $exitCode = Artisan::call('db:seed --class=UsersTableSeeder');
    return '<h1>Users table has been cleared</h1>';
});
Auth::routes();
Route::group( ['middleware' => ['auth']], function() {
    Route::resource('users', 'UserController');
    Route::post('/users/search', 'UserController@search')->name('users.search');
    Route::resource('roles', 'RoleController');
    Route::resource('permissions', 'PermissionController');
    Route::resource('configurations', 'ConfigurationController');
    Route::resource('sms_templates', 'SmsTemplateController');
    Route::resource('consultant_types', 'ConsultantTypeController');
    Route::get('audit_logs', 'AuditLogController@index')->name('audit_logs.index');
    Route::match(['get', 'post'], '/report/pf_summary', 'ReportController@pf_summary')->name('reports.pf_summary');
    // Route::get('/physician/dashboard', function() {
    //     return view('physician.dashboard');
    // });
    Route::get('/physician/dashboard', array('as' => 'physician.dashboard', 'uses' => function() {
        return view('physician.dashboard');
    }));
    Route::post('/physician/get_patients', 'PhysicianController@get_patients')->name('physician.get_patients');
    Route::get('/physician/view_transaction/{external_id}/{patient_id}/{practitioner_id}', 'PhysicianController@view_transaction')->name('physician.view_transaction');
    Route::get('/physician/get_transaction_details/{external_id}/{patient_id}/{practitioner_id}', 'PhysicianController@get_transaction_details')->name('physician.get_transaction_details');
    Route::post('/physician/set_professional_fee', 'PhysicianController@set_professional_fee')->name('physician.set_professional_fee');
    Route::post('/physician/get_remaining_time', 'PhysicianController@get_remaining_time')->name('physician.get_remaining_time');
    Route::get('/physician/toggle_display_pf/{pcp_id}/{show_pf}', 'PatientCareProviderController@toggle_display_pf')->name('physician.get_remaining_time');
    // Route::get('/physicians/toggle_display_pf/{pcp_id}/{show_pf}', 'PatientCareProviderController@toggle_display_pf')->name('physician.get_remaining_time');
    Route::post('password/change', 'UserController@password_change')->name('user.password_change');
    Route::patch('user/default_pf_type', 'UserController@default_pf_type')->name('user.default_pf_type');
});
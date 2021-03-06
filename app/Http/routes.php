<?php

Route::get('/', function () {
    if(Auth::check())
        return redirect('/profile');
    else
        return redirect('/login');
});

Route::get('login', 'Auth\AuthController@getLogin');
Route::post('login', 'Auth\AuthController@postLogin');
Route::get('logout', 'Auth\AuthController@getLogout');
Route::get('ajax/timezone', 'CommonController@timezone');

Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

Route::group(['middleware' => 'auth'], function () {
    Route::get('profile', 'UsersController@show');
    Route::get('profile/edit', 'UsersController@edit');
    Route::get('profile/notifications', 'UsersController@showAllNotifications');
    Route::get('profile/messages', 'UsersController@showMessages');
    Route::get('profile/messages/compose', 'UsersController@sendMessageForm');
    Route::put('profile/messages/compose', 'UsersController@sendMessage');
    Route::get('profile/messages/{id}', 'UsersController@readMessage');
    Route::delete('profile/messages/{id}', 'UsersController@deleteMessage');

    Route::group(['middleware' => 'auth.supadmin'], function () {
        Route::get('/companies/{company_id}/shifts', 'CompaniesController@shiftsShow');
        Route::put('/companies/{company_id}/shifts', 'CompaniesController@shiftsUpdate');
        Route::get('/companies/{company_id}/payment', 'CompaniesController@paymentShow');
        Route::post('/companies/{company_id}/payment', 'CompaniesController@paymentStore');
        Route::post('/companies/{company_id}/payment/custom', 'CompaniesController@paymentCustomStore');
        Route::delete('/companies/{company_id}/payment/custom/{payment_id}', 'CompaniesController@paymentCustomDestroy');
        Route::resource('companies', 'CompaniesController', ['only' => ['index', 'store', 'update', 'destroy']]);
    });

    Route::group(['middleware' => 'auth.notWorker'], function () {
        Route::post('/users/{user_id}/roster', 'RostersController@store');

        Route::get('payments', 'PaymentsController@index');
        Route::get('payments/user/{user_id}/shifts', 'PaymentsController@edit');
        Route::put('payments/user/{user_id}/shifts', 'PaymentsController@update');
        Route::put('/companies/{company_id}/link', 'UsersController@linkUser');
    });

    Route::group(['middleware' => 'auth.worker'], function () {
        Route::get('availability', 'AvailabilityController@index');
        Route::post('availability', 'AvailabilityController@create');
        Route::get('availability/events', 'AvailabilityController@events');
        Route::post('availability/events/{id}', 'AvailabilityController@update');
        Route::delete('availability/events/{id}', 'AvailabilityController@destroy');
        Route::get('availability/google', 'AvailabilityController@googleList');
        Route::get('availability/google/{user_id}', 'AvailabilityController@googleListCalendars');
        Route::post('availability/google/{user_id}', 'AvailabilityController@googleSaveCalendar');
        Route::delete('availability/google/{user_id}', 'AvailabilityController@deleteGoogleProfile');
        Route::get('auth/google/provider', 'Auth\AuthController@redirectToGoogleProvider');
        Route::get('auth/google/callback', 'Auth\AuthController@handleGoogleProviderCallback');
    });

    Route::resource('users', 'UsersController');
    Route::get('/users/{user_id}/notifications', 'UsersController@showAllNotifications');
    Route::delete('/users/{user_id}/unlink/{company_id}', 'UsersController@unlinkCompany');
    Route::delete('/users/{user_id}/unlink', 'UsersController@unlinkAllCompanies');
    Route::get('/users/{id}/edit/link', 'UsersController@linkCompanyFrom');
    Route::put('/users/{id}/edit/link', 'UsersController@linkCompany');

    Route::get('/rosters', 'RostersController@index');
    Route::put('/rosters/{event_id}', 'RostersController@update');
    Route::get('/rosters/workers/{company_id}', 'RostersController@workers');
    Route::get('/rosters/events/{company_id}', 'RostersController@events');
    Route::post('/rosters/events/{event_id}', 'RostersController@updateEvent');
    Route::get('/rosters/event/{event_id}', 'RostersController@getEvent');
    Route::get('/rosters/event/{event_id}/unlinkedUsers', 'RostersController@unlinkedUsers');
    Route::post('/rosters/event/{event_id}/users', 'RostersController@addUsers');
});

Route::get('avatar/{filename?}', function ($filename = null) {
    $filename = str_replace(".", "/", $filename);
    $filename = str_replace("/png", ".png", $filename);
    $filename = str_replace("/jpg", ".jpg", $filename);
    $filename = str_replace("/gif", ".gif", $filename);
    $path = storage_path("app") . '/avatars/' . $filename;

    if(!file_exists($path) || $filename == null){
        $path = storage_path("app") . '/avatars/no_avatar.png';
    }

    try {
        $file = File::get($path);
    }catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e){
        $file = File::get(storage_path("app") . '/avatars/no_avatar.png');
    }
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});
Route::get('cv/{filename}', function ($filename) {
    $path = storage_path("app") . '/cv/' . $filename;

    if(!file_exists($path) || $filename == null){
        abort(404, "File not found");
    }

    return response()->download($path);
});
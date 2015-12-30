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

Route::group(['middleware' => 'auth'], function () {
    Route::get('profile', 'UsersController@show');

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
    });
    Route::resource('users', 'UsersController');
    Route::get('/rosters', 'RostersController@index');
    Route::put('/rosters/{event_id}', 'RostersController@update');
    Route::get('/rosters/workers/{company_id}', 'RostersController@workers');
    Route::get('/rosters/events/{company_id}', 'RostersController@events');
    Route::post('/rosters/events/{event_id}', 'RostersController@updateEvent');

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
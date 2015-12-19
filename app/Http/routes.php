<?php

Route::get('/', function () {
    if(Auth::check())
        return redirect('/dashboard');
    else
        return redirect('/login');
});

Route::get('login', 'Auth\AuthController@getLogin');
Route::post('login', 'Auth\AuthController@postLogin');
Route::get('logout', 'Auth\AuthController@getLogout');

Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', function () {
        return view('dashboard');
    });
});

Route::get('avatar/{filename?}', function ($filename = null) {
    $filename = str_replace(".", "/", $filename);
    $filename = str_replace("/png", ".png", $filename);
    $filename = str_replace("/jpg", ".jpg", $filename);
    $filename = str_replace("/gif", ".gif", $filename);
    $path = storage_path("app") . '/' . $filename;

    if(!file_exists($path) || $filename == null){
        $path = storage_path("avatar") . '/no_avatar.png';
    }

    try {
        $file = File::get($path);
    }catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e){
        $file = File::get(storage_path("avatar") . '/no_avatar.png');
    }
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});
<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//默认页面
Route::get('/', function () {
    return 'wangguojiyuan_api';
});

Route::group(['prefix' => 'api', 'namespace' => 'API'], function () {
    Route::controllers([
        'run' => 'RunController',
        'upload' => 'UploadController',
    ]);

    Route::group(['prefix' => 'applet', 'namespace' => 'Applet'], function () {
        Route::controllers([
            'wx' => 'WeiXinController',
        ]);

    });

});

Route::group(['prefix' => 'back', 'namespace' => 'Callback'], function () {
    Route::controllers([
        'task' => 'TaskController',
    ]);
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});

Route::get('/test', function () {
    return 'sdfdsf';
});

Route::post('/test', function () {
    return 'sdfdsf';
});

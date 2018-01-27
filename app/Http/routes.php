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
use App\Service\RunService;
use App\Service\SubMailService;

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
//    $ret = SubMailService::message('18668179721', '【王国云助手】验证码：1111 ，为了保证安全，打死也不能告诉别人哦。');
//    $ret = SubMailService::xmessage('18668179721', 'sadsadsadsadsad3rf32f');

//    $resultMsg = '';
//    $serviceResult = RunService::voice('18668179721', '王国语音通知，您的保护盾马上到期，请及时处理。', 1, $resultMsg);
//    echo $resultMsg;
//    return var_dump($serviceResult);
});

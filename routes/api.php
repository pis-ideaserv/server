<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api'], function () {

    Route::group(['prefix' => 'auth'],function (){
        Route::post('login',
            [
                'as'    => 'api.auth.login',
                'uses'  =>  'AuthController@login'
            ]
        );

        Route::post('refresh',
            [
                'as'            =>  'api.auth.refresh',
                'uses'          =>  'AuthController@refresh',
            ]
        );

        Route::post('check',
            [
                'as'            =>  'api.auth.check',
                'uses'          =>  'AuthController@check',
                'middleware'    =>  'jwt'
            ]
        );

        Route::post('logout',
            [
                'as'            =>  'api.auth.logout',
                'uses'          =>  'AuthController@logout',
                'middleware'    =>  'jwt'
            ]
        );
    });


    Route::group(['middleware' => 'jwt'],function(){
        Route::resource('users', 'UserController',
            [
                'names' =>
                    [
                        'index'     => 'api.users.indexes',
                        'show'      => 'api.users.show',
                        'store'     => 'api.users.store',
                        'update'    => 'api.users.update',
                        'destroy'   => 'api.users.destroy'
                    ],
                'except' => ['create', 'edit'],
                'parameters' => ['user' => 'user_id']
            ]
        );

        Route::resource('products', 'ProductController',
            [
                'names' =>
                    [
                        'index'     => 'api.products.indexes',
                        'show'      => 'api.products.show',
                        'store'     => 'api.products.store',
                        'update'    => 'api.products.update',
                        'destroy'   => 'api.products.destroy'
                    ],
                'except' => ['create', 'edit'],
                'parameters' => ['user' => 'user_id']
            ]
        );

        Route::resource('product-master-list', 'ProductMasterListController',
            [
                'names' =>
                    [
                        'index'     => 'api.products.indexes',
                        'show'      => 'api.products.show',
                        'store'     => 'api.products.store',
                        'update'    => 'api.products.update',
                        'destroy'   => 'api.products.destroy'
                    ],
                'except' => ['create', 'edit'],
                'parameters' => ['user' => 'user_id']
            ]
        );

        Route::resource('category', 'CategoryController',
            [
                'names' =>
                    [
                        'index'     => 'api.products.indexes',
                        'show'      => 'api.products.show',
                        'store'     => 'api.products.store',
                        'update'    => 'api.products.update',
                        'destroy'   => 'api.products.destroy'
                    ],
                'except' => ['create', 'edit'],
                'parameters' => ['user' => 'user_id']
            ]
        );


        Route::resource('suppliers', 'SupplierController',
            [
                'names' =>
                    [
                        'index'     => 'api.products.indexes',
                        'show'      => 'api.products.show',
                        'store'     => 'api.products.store',
                        'update'    => 'api.products.update',
                        'destroy'   => 'api.products.destroy'
                    ],
                'except' => ['create', 'edit'],
                'parameters' => ['user' => 'user_id']
            ]
        );

        Route::resource('logs', 'LogController',
            [
                'names' =>
                    [
                        'index'     => 'api.products.indexes',
                        'show'      => 'api.products.show',
                        'store'     => 'api.products.store',
                        'update'    => 'api.products.update',
                        'destroy'   => 'api.products.destroy'
                    ],
                'except' => ['create', 'edit'],
                'parameters' => ['user' => 'user_id']
            ]
        );

        Route::resource('status', 'StatusController',
            [
                'names' =>
                    [
                        'index'     => 'api.status.indexes',
                    ],
                'except' => ['create', 'edit'],
                'parameters' => ['user' => 'user_id']
            ]
        );

        Route::resource('notification','NotificationController',
            [
                'name' =>
                    [
                        'index'     => 'api.notification.indexes',
                        'show'      => 'api.notification.show',
                        'store'     => 'api.notification.store',
                    ],
                'except' => ['create', 'edit','delete'],
                'parameters' => ['user' => 'user_id']
            ]
        );
    });


    Route::delete('/file/{name}','FileUploadController@destroy');
    Route::get('/file/{name}','FileUploadController@index');
    Route::post('/file/{any?}','FileUploadController@upload')->where('any', '.*'); 
    Route::patch('/file/{any?}','FileUploadController@upload')->where('any', '.*');
});

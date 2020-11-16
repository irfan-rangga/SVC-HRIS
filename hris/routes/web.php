<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'v1'], function () use ($router) {

  $router->post('versions', 'VersionController@index');
  //Absensi User
  $router->post('presences', 'PresenceController@store');
	$router->post('presences/users/{id}', 'PresenceController@absensiUserId');

	$router->group(['prefix' => 'pieces'], function () use ($router) {
		//Potongan terlambat
	  $router->get('lates/{id}', 'PieceController@late');
	  //Potongan Ijin
	  $router->get('permissions/{id}', 'PieceController@permission');
	  //Potongan Pembelian
	  $router->get('purchases/{id}', 'PieceController@purchase');
	  //Potongan BPJS
	  $router->get('bpjs/{id}', 'PieceController@bpjs');
	  //Potongan Pinjaman
	  $router->get('loans/{id}', 'PieceController@loan');

	});


	$router->group(['prefix' => 'submissions'], function () use ($router) {
  	//Cuti
	  $router->get('leave', 'LeaveController@index');
	  //Ijin
	  $router->post('permissions/in-out', 'PermissionController@permissionInOut');
	  $router->get('permissions/in-out', 'PermissionController@getPermissionInOut');
	});

});

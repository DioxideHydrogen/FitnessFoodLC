<?php

use App\Http\Controllers\CronJobController;
use App\Http\Controllers\ProductController;
use App\Models\Bootstrap;
use App\Models\CronJob;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function(){

	$read = Bootstrap::all();

	$bootstrap = Bootstrap::all()->last();
	
	$write = $bootstrap->update();

	$cronJob = CronJob::all()->last();

	return response()->json([
		'read' => (bool) $read,
		'write' => (bool) $write,
		'last_cron' => @$cronJob->created_at?:false,
		'memory_used' => memory_get_usage()
	]);

});

Route::group(['prefix' => 'products'], function(){
	Route::get('/', [ProductController::class, 'index']);
	Route::get('/{code}', [ProductController::class, 'show']);
	Route::put('/{code}', [ProductController::class, 'update']);
	Route::delete('/{code}', [ProductController::class, 'destroy']);
});

Route::group(['prefix' => 'cronjob'], function(){
	Route::get('/products/update', [CronJobController::class, 'updateProducts']);
});
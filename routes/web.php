<?php

use App\Http\Controllers\BotManController;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Route;
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

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/run', function () {
//     $notify = new NotificationService();
//     return $notify->run();
// });

Route::match(['get', 'post'], 'botman', [BotManController::class, 'handle']);
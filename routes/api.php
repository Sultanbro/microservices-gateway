<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Booking\BookingController;
use App\Http\Controllers\Api\Booking\BookingFileController;
use App\Http\Controllers\Api\Booking\BookingUsersController;
use App\Http\Controllers\Api\Booking\RoomController;
use App\Http\Controllers\Api\Centcoin\CentcoinApplyController;
use App\Http\Controllers\Api\Centcoin\CentcoinController;
use App\Http\Controllers\Api\Email\EmailController;
use App\Http\Controllers\Api\Post\CommentController;
use App\Http\Controllers\Api\Post\LikeController;
use App\Http\Controllers\Api\Post\PostController;
use App\Http\Controllers\Api\Services\DocumentDesigner\DocumentDesignerController;
use App\Http\Controllers\Api\User\Avatar\AvatarController;
use App\Http\Controllers\Api\User\Role\PermissionController;
use App\Http\Controllers\Api\User\Role\PermissionGroupController;
use App\Http\Controllers\Api\User\Role\RoleController;
use App\Http\Controllers\Api\User\Role\UserRoleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\WriteBase\CareerUserController;
use App\Http\Controllers\Api\WriteBase\CityController;
use App\Http\Controllers\Api\WriteBase\ClientBaseController;
use App\Http\Controllers\Api\WriteBase\DictisController;
use App\Http\Controllers\Api\WriteBase\DutyController;
use App\Http\Controllers\Api\WriteBase\RegionController;
use App\Http\Controllers\Api\WriteBase\StaffController;
use App\Http\Controllers\Command\CommandController;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Services\MycentQuotation;

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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});



//Auth route
Route::post('/register', [RegisterController::class, 'index'])->withoutMiddleware('auth.bearer');
Route::post('/login', [LoginController::class, 'index'])->withoutMiddleware('auth.bearer');
Route::post('/logout', [LogoutController::class, 'index'])->middleware('auth.bearer');
Route::post('/reset/', [RegisterController::class, 'resetPassword'])->withoutMiddleware('auth.bearer');

//DocumentDesignerService route
//Handbook route
Route::resource('handbook', DocumentDesignerController::class);
Route::post('handbook/joint', [DocumentDesignerController::class, 'store']);
Route::post('handbook/dicti', [DocumentDesignerController::class, 'store']);
Route::post('handbook/dictiSaveInKias', [DocumentDesignerController::class, 'store']);

//Document Constructor route
Route::resource('document-constructor', DocumentDesignerController::class);

//Document Row Column Editor
Route::resource('row-column-editor', DocumentDesignerController::class);

//Button Editor route
Route::resource('button-editor', DocumentDesignerController::class);

//Numerator route
Route::resource('numerator', DocumentDesignerController::class);
Route::post('numerator/get/on', [DocumentDesignerController::class, 'store']);

//Attribute route
Route::resource('attribute', DocumentDesignerController::class);

//Document Row route
Route::resource('document-row', DocumentDesignerController::class);

//Business Process route
Route::resource('business_process', DocumentDesignerController::class);

//MycentQuotation Service route
Route::post('full-editor', [MycentQuotation\MycentQuotationController::class,'store']);

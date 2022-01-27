<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LabelController;



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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'register']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('forgotpassword', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('resetpassword', [ForgotPasswordController::class, 'resetPassword']);

    Route::post('createnote', [NoteController::class, 'createNote']);
    Route::get('displaynote', [NoteController::class, 'displayNoteById']);
    Route::post('deletenote', [NoteController::class, 'deleteNoteById']);
    Route::post('updatenote', [NoteController::class, 'updateNoteById']);
    Route::get('paginatenote', [NoteController::class, 'paginationNote']);

    Route::post('createlable', [LabelController::class, 'createLabel']);
    Route::post('addlabelbynoteid', [LabelController::class, 'addLabelByNoteId']);
    Route::get('displaylable', [LabelController::class, 'displayLabelById']);
    Route::post('updatelable', [LabelController::class, 'updateLabelById']);
    Route::post('deletelable', [LabelController::class, 'deleteLabelById']);
  
}); 

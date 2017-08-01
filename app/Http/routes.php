<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('pickinstructor', 'DisplayController@pickInstructor')->middleware('auth');
Route::get('instructor/{id}', 'DisplayController@showInstructor')->middleware('auth');
Route::get('dept/{dept}', 'DisplayController@getDept');
Route::get('level/{num}', 'DisplayController@getLevel');
Route::get('alllevels', 'DisplayController@getAllLevels');
Route::get('alldepts', 'DisplayController@getAllDepts');
Route::get('all', 'DisplayController@getAll');
Route::get('samecourses/{dept}/{num}', 'DisplayController@getAllWithSameCourse')->middleware('auth');
Route::get('single/{id}','DisplayController@getHashedId');
Route::get('summary/{id}','DisplayController@getInstructorSummary')->middleware('auth');

Route::auth();

Route::get('/home', 'HomeController@index');

<?php

Route::get('/', 'HomeController@index');
Route::get('login', 'AuthController@login');
Route::get('callback', 'AuthController@callback');

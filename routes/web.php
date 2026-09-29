<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('accueil');
Route::livewire('/quiz/{epreuve}', 'pages::quiz')->name('quiz');
Route::livewire('/bilan', 'pages::bilan')->name('bilan');

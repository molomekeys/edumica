<?php

use App\Http\Controllers\ConnexionController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('accueil');
Route::livewire('/quiz/{epreuve}', 'pages::quiz')->name('quiz');
Route::livewire('/bilan', 'pages::bilan')->name('bilan');

Route::middleware('guest')->group(function () {
    Route::livewire('/connexion', 'pages::connexion')->name('connexion');
    Route::post('/connexion/demo/{role}', [ConnexionController::class, 'demo'])->name('connexion.demo');
});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [ConnexionController::class, 'deconnexion'])->name('deconnexion');

    Route::livewire('/espace', 'pages::espace')->name('espace');
    Route::livewire('/test-blanc/{epreuve}', 'pages::test-blanc')->name('test-blanc');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::livewire('/', 'pages::admin.questions')->name('questions');
        Route::livewire('/questions/creer', 'pages::admin.question')->name('questions.creer');
        Route::livewire('/questions/{question}', 'pages::admin.question')->name('questions.modifier');
    });
});

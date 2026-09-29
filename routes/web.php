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
    Route::livewire('/test-blanc', 'pages::test-blanc')->name('test-blanc.complet');
    Route::livewire('/test-blanc/{epreuve}', 'pages::test-blanc')->name('test-blanc');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::livewire('/', 'pages::admin.questions')->name('questions');
        Route::livewire('/questions/creer', 'pages::admin.question')->name('questions.creer');
        Route::livewire('/questions/{question}', 'pages::admin.question')->name('questions.modifier');
    });
});

Route::livewire('/epreuves', 'pages::epreuves')->name('epreuves');
Route::livewire('/epreuves/{epreuve}', 'pages::epreuve')->name('epreuve');
Route::livewire('/tests-blancs', 'pages::tests-blancs')->name('tests-blancs');
Route::livewire('/scores-nclc', 'pages::scores')->name('scores');
Route::livewire('/tarifs', 'pages::tarifs')->name('tarifs');
Route::livewire('/faq', 'pages::faq')->name('faq');
Route::livewire('/contact', 'pages::contact')->name('contact');

Route::livewire('/mentions-legales', 'pages::mentions-legales')->name('mentions-legales');
Route::livewire('/cgv', 'pages::cgv')->name('cgv');
Route::livewire('/confidentialite', 'pages::confidentialite')->name('confidentialite');

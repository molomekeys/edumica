<?php

use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\EspaceController;
use App\Http\Controllers\TestBlancController;
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
});

// Dashboards (Inertia + React) : espace utilisateur, test blanc et panel admin.
Route::middleware(['auth', 'inertia'])->group(function () {
    Route::get('/espace', [EspaceController::class, 'index'])->name('espace');
    Route::get('/espace/tests-blancs', [EspaceController::class, 'testsBlancs'])->name('espace.tests-blancs');
    Route::get('/espace/resultats', [EspaceController::class, 'resultats'])->name('espace.resultats');

    Route::get('/test-blanc', [TestBlancController::class, 'show'])->name('test-blanc.complet');
    Route::get('/test-blanc/{epreuve}', [TestBlancController::class, 'show'])->name('test-blanc');
    Route::put('/test-blanc/tentatives/{tentative}', [TestBlancController::class, 'sauvegarder'])->name('test-blanc.sauvegarder');
    Route::post('/test-blanc/tentatives/{tentative}/terminer', [TestBlancController::class, 'terminer'])->name('test-blanc.terminer');
    Route::delete('/test-blanc/tentatives/{tentative}', [TestBlancController::class, 'abandonner'])->name('test-blanc.abandonner');
    Route::get('/test-blanc/tentatives/{tentative}/resultat', [TestBlancController::class, 'resultat'])->name('test-blanc.resultat');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [QuestionController::class, 'index'])->name('questions');
        Route::get('/questions/creer', [QuestionController::class, 'create'])->name('questions.creer');
        Route::post('/questions', [QuestionController::class, 'store'])->name('questions.enregistrer');
        Route::get('/questions/{question}', [QuestionController::class, 'edit'])->name('questions.modifier');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.mettre-a-jour');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.supprimer');
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

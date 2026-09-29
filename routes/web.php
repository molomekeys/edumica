<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('accueil');
Route::livewire('/quiz/{epreuve}', 'pages::quiz')->name('quiz');
Route::livewire('/bilan', 'pages::bilan')->name('bilan');

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

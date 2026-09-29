<?php

use App\Http\Middleware\EstAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['admin' => EstAdmin::class, 'inertia' => HandleInertiaRequests::class]);
        $middleware->redirectGuestsTo(fn () => route('connexion'));
        $middleware->redirectUsersTo(fn () => route('espace'));
        // État de la sidebar des dashboards, écrit par le navigateur.
        $middleware->encryptCookies(except: ['sidebar_state']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Session expirée pendant une visite Inertia : la page de connexion (Livewire)
        // doit se charger entièrement, pas dans la modale d'erreur d'Inertia.
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->header('X-Inertia')) {
                return Inertia::location(route('connexion'));
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

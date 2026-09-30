<?php

namespace App\Http\Middleware;

use App\Support\Langue;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\Response;

/**
 * Langue de l'interface : celle choisie par le visiteur (cookie), sinon celle de son navigateur,
 * sinon le français. Le panel admin reste en français.
 */
class DefinirLangue
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->langue($request));

        return $next($request);
    }

    private function langue(Request $request): string
    {
        // Une requête Livewire arrive sur /livewire/update : on regarde la page d'où elle vient.
        $chemin = Livewire::isLivewireRequest() ? Livewire::originalPath() : $request->path();

        if ($chemin === 'admin' || str_starts_with($chemin, 'admin/')) {
            return Langue::PAR_DEFAUT;
        }

        $choisie = $request->cookie(Langue::COOKIE);

        if (is_string($choisie) && Langue::existe($choisie)) {
            return $choisie;
        }

        return $request->getPreferredLanguage(Langue::CODES) ?? Langue::PAR_DEFAUT;
    }
}

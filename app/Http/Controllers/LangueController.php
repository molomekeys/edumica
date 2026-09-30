<?php

namespace App\Http\Controllers;

use App\Support\Langue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

/**
 * Sélecteur de langue : garde le choix un an et revient sur la page d'où vient le visiteur.
 */
class LangueController extends Controller
{
    public function __invoke(string $langue): RedirectResponse
    {
        abort_unless(Langue::existe($langue), 404);

        Cookie::queue(Langue::COOKIE, $langue, 60 * 24 * 365);

        return redirect()->back(fallback: route('accueil'));
    }
}

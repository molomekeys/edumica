<?php

namespace App\Http\Middleware;

use App\Models\Epreuve;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

/**
 * Dashboards Inertia (espace utilisateur, test blanc, admin). Le site public reste en Livewire :
 * ce middleware n'est appliqué qu'aux routes des dashboards.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * Vue racine dédiée, distincte du layout Livewire (layouts/app.blade.php).
     *
     * @var string
     */
    protected $rootView = 'inertia';

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $request->user() ? [
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'initiales' => $request->user()->initiales(),
                    'is_admin' => $request->user()->is_admin,
                ] : null,
            ],
            'flash' => fn () => $this->flash($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // Sous-menu des épreuves dans la navigation admin.
            'navigation' => fn () => [
                'epreuves' => $request->routeIs('admin.*')
                    ? Epreuve::orderBy('ordre')->withCount('questions')->get(['slug', 'nom'])->map->only(['slug', 'nom', 'questions_count'])
                    : [],
            ],
        ];
    }

    /**
     * Messages flash de la session, affichés en toast. L'identifiant évite de réafficher
     * un message quand la page revient de l'historique du navigateur.
     *
     * @return array{id: string, succes: ?string, erreur: ?string}|null
     */
    private function flash(Request $request): ?array
    {
        $succes = $request->session()->get('succes');
        $erreur = $request->session()->get('erreur');

        if (! $succes && ! $erreur) {
            return null;
        }

        return ['id' => (string) Str::uuid(), 'succes' => $succes, 'erreur' => $erreur];
    }
}

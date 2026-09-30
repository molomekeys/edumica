<?php

namespace App\Policies;

use App\Models\Tentative;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Un test blanc n'est visible et modifiable que par la personne qui le passe.
 * Pour les autres, il n'existe pas (404 plutôt que 403).
 */
class TentativePolicy
{
    public function view(User $user, Tentative $tentative): Response
    {
        return $this->auteur($user, $tentative);
    }

    public function update(User $user, Tentative $tentative): Response
    {
        return $this->auteur($user, $tentative);
    }

    public function delete(User $user, Tentative $tentative): Response
    {
        return $this->auteur($user, $tentative);
    }

    private function auteur(User $user, Tentative $tentative): Response
    {
        return $tentative->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }
}

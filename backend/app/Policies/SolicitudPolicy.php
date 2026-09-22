<?php

namespace App\Policies;

use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SolicitudPolicy
{
    public function view(User $user, Solicitud $solicitud): Response
    {
        return $user->id === $solicitud->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function update(User $user, Solicitud $solicitud): Response
    {
        if ($user->id !== $solicitud->user_id) {
            return Response::denyAsNotFound();
        }

        return $solicitud->esEditable()
            ? Response::allow()
            : Response::deny('No se puede modificar una solicitud que ya está en atención o finalizada.', 422);
    }

    public function delete(User $user, Solicitud $solicitud): Response
    {
        if ($user->id !== $solicitud->user_id) {
            return Response::denyAsNotFound();
        }

        return $solicitud->esCancelable()
            ? Response::allow()
            : Response::deny('No se puede cancelar una solicitud asignada o en proceso de resolución.', 422);
    }
}

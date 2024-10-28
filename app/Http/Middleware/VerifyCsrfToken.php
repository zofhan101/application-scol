<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'authAdmin',
        'niveaux_par_parcours',
        'ue/create_ue',
        'ue/liste_ue',
        'ue/liste_ec',
        'ue/create_ec',
        'ue/ajouter_ue_ec',
        'ue/liste_ue_ec',
        'ue/supprimer_ue_ec',
        'notes/get_operation_par_examen',
        'notes/ouvrir_saisie_note',
        'notes/enregistrer_note',
        'notes/verrouiller_saisie_note',
        'notes/get_note',
        'notes/modifier_note',
        'notes/ouvrir_verification_note',
        'notes/verrouiller_verification_note'
    ];
}

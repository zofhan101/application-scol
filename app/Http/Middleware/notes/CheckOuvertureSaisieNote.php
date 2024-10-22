<?php

namespace App\Http\Middleware\notes;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\notes\Operation_sur_examen;
use App\Models\AU\AU;
use Illuminate\Support\Facades\Auth;

class CheckOuvertureSaisieNote
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $au_courant = AU::get_au_en_cours();
        $user = Auth::user();

        $operations = Operation_sur_examen::get_saisies_ouvertes($au_courant->id_au);

        foreach($operations as $operation){
            //acces à partir de chef de division quand saisie ouverte mais non verrouillée
            if($operation->date_ouverture_saisie_note !=  null && $operation->date_cloture_saisie_note == null && $user->role->rang_role >= 0){
                return $next($request);
            }
            //accès privilégié quand saisie cloturée mais résultats non validés
            else if($operation->date_ouverture_saisie_note !=  null && $operation->date_cloture_saisie_note != null && $operation->date_resultats == null && $user->role->rang_role >=40){
                return redirect('authentification_contradictoire');
            }
        }
        return redirect(route('acces_refusé'));
    }
}

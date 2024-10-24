<?php

namespace App\Http\Middleware\notes;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\notes\Operation_sur_examen;
use App\Models\AU\AU;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
        }

        foreach($operations as $operation){
            //accès privilégié quand saisie cloturée mais résultats non validés
            if($operation->date_ouverture_saisie_note !=  null && $operation->date_cloture_saisie_note != null && $operation->date_resultats == null && $user->role->rang_role >=40){
                $user2;
                $cookie = $request->cookie('auth_cont');
                if(Session::has('user2')){
                    $user2 = Session::get('user2');
                    if($user2->role->rang_role>=40 && $cookie != null ){
                        return $next($request);
                    }
                    else{
                        Session::put('url.intended', request()->fullUrl());
                        return redirect(route('authentification_contradictoire.form'));
                    }
                }
                else{
                    Session::put('url.intended', request()->fullUrl());
                    return redirect(route('authentification_contradictoire.form'));
                }
            }
        }
        return redirect(route('acces_refuse'));
    }
}

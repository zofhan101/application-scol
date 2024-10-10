<?php

namespace App\Models\UE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\AU\AU;
use Illuminate\Database\UniqueConstraintViolationException;
use Exception;
use stdClass;



class Unite_enseignement extends Model
{
    use HasFactory;
    protected $table = 'unite_enseignement';
    protected $primaryKey = 'id_unite_enseignement';
    protected $fillable=[
        'nom_unite_enseignement'
    ];

    public static function supprimer_ue_ec($suppressions){
        foreach($suppressions as $id_ue_ec){
            DB::delete('
                delete from ue_ec_parcours_niveau_au where id_ue_ec = ?
            ',[$id_ue_ec]);
        }
    }

    public static function ajouter_ue_ec($ajout){
        //insérer dans la base les nouvelles sélections
        $id_ue;
        $id_ec;
        $id_parcours;
        $id_niveau;
        $id_examen_par_au;
        $coefficient;

        $au = AU::get_au_en_cours();
        $id_au = $au->id_au;

        $id_ue = $ajout['ue']['id_ue'];
        $id_parcours = $ajout['id_parcours'];
        $id_niveau = $ajout ['id_niveau'];
        $id_examen_par_au = $ajout['ue']['id_examen_par_au'];
        $coefficient = $ajout['ue']['coef'];
        foreach($ajout['ecs'] as $ec){
            $id_ec = $ec['id_ec'];
            rescue(
                function() use($id_ue, $id_parcours, $id_niveau, $id_examen_par_au, $coefficient, $id_ec, $id_au){
                    DB::insert('
                        insert into ue_ec_parcours_niveau_au(id_unite_enseignement, id_parcours, id_niveau, id_examen_par_au, coefficient, id_element_constitutif, id_au) values(?,?,?,?,?,?,?);
                        ',  [$id_ue, $id_parcours, $id_niveau, $id_examen_par_au, $coefficient, $id_ec, $id_au]
                    );
                },
                function(Exception $ex){
                    echo $ex->getMessage();
                },
                false
            );
        }



    }

    public static function get_liste_ue_ec($id_parcours, $id_niveau, $id_au){
        $liste_ue_ec = DB::select('select * from v_liste_ue_ec where id_parcours=? and id_niveau = ? and id_au=? order by id_unite_enseignement asc, id_session_examen, id_element_constitutif asc',[$id_parcours, $id_niveau, $id_au]);
        $id_ue_prec ='';
        $id_session_prec='';
        $res = [];
        $ue;
        $ec;
        if(!empty($liste_ue_ec)){
            foreach($liste_ue_ec as $ue_ec){
                if($ue_ec->id_unite_enseignement != $id_ue_prec || $ue_ec->id_session_examen != $id_session_prec){
                    $ue = new stdClass();
                    $res[] = $ue;
                    $ue->id_ue = $ue_ec->id_unite_enseignement;
                    $ue->nom_ue = $ue_ec->nom_unite_enseignement;
                    $ue->id_session_examen = $ue_ec->id_session_examen;
                    $ue->nom_session_examen = $ue_ec->nom_session_examen;
                    $ue->coef= $ue_ec->coefficient;
                    $ue->ecs=[];
                }
                $ec = new stdClass();
                $ec->id_ec = $ue_ec->id_element_constitutif;
                $ec->nom_ec = $ue_ec->nom_element_constitutif;
                $ec->id_ue_ec = $ue_ec->id_ue_ec;
                $ue->ecs[] = $ec;

            }
            return $res;
        }
        throw new Exception('Aucune U.E. n\'a encore été enregistrée pour le parcours, le niveau et l\'A.U. sélectionnés.');
    }
}

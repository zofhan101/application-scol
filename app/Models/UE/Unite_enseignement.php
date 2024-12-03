<?php

namespace App\Models\UE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\AU\AU;
use Illuminate\Database\UniqueConstraintViolationException;
use Exception;
use stdClass;
use DNS1D;



class Unite_enseignement extends Model
{
    use HasFactory;
    protected $table = 'unite_enseignement';
    protected $primaryKey = 'id_unite_enseignement';
    protected $fillable=[
        'nom_unite_enseignement'
    ];

    public static function get_liste_ec($id_parcours, $id_niveau, $id_ue, $id_au){
        try {
            $liste_ec = DB::select('
                select distinct id_ue_ec, id_element_constitutif, nom_element_constitutif from  v_liste_ue_ec where id_au = ? and id_parcours = ? and id_niveau = ? and id_unite_enseignement = ?
            ', [$id_au, $id_parcours, $id_niveau, $id_ue]);
            if(empty($liste_ec))
                throw new Exception('Aucune EC ne correspond aux AU, parcours ,niveau et UE sélectionnés');
            return $liste_ec;
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public static function get_liste_ue($id_parcours, $id_niveau, $id_au){
        try {
            $liste_ue = DB::select('
                select distinct id_unite_enseignement, nom_unite_enseignement from  v_liste_ue_ec where id_au = ? and id_parcours = ? and id_niveau = ?
            ', [$id_au, $id_parcours, $id_niveau]);
            if(empty($liste_ue))
                throw new Exception('Aucune UE ne correspond aux AU, parcours et niveau sélectionnés');
            return $liste_ue;
        } catch (\Exception $th) {
            throw $th;
        }
    }


    public static function get_ue_ec_by_id($id_ue_ec){
        return DB::select('
                select * from ue_ec_parcours_niveau_au where id_ue_ec = ?
            ', [$id_ue_ec]);
    }


    public static function get_liste_ue_ec_code_barre($id_au){
        $liste_ue_ec = DB::select('
            select *
            from v_liste_ue_ec_avec_nbr_inscrits
            where id_au = ?
            order by id_session_examen asc, id_mention asc, id_parcours asc, id_niveau asc, id_unite_enseignement asc
        ',[$id_au]);
        if(!empty($liste_ue_ec)){
            $ue_ec0 = $liste_ue_ec[0];

            $res = [];
            $result = [];
            $res_assoc = [];

            $evaluation = new stdClass();
            $evaluation->id_session_examen = $ue_ec0->id_session_examen;
            $evaluation->nom_session_examen = $ue_ec0->nom_session_examen;
            $id_session_examen_prec = $ue_ec0->id_session_examen;
            $id_session_examen = $ue_ec0->id_session_examen;

            $mentions = [];
            $mention = new stdClass();
            $mention->id_mention = $ue_ec0->id_mention;
            $mention->nom_mention = $ue_ec0->nom_mention;
            $id_mention_prec = $ue_ec0->id_mention;
            $id_mention = $ue_ec0->id_mention;

            $parcours = [];
            $parcour = new stdClass();
            $parcour->id_parcours = $ue_ec0->id_parcours;
            $parcour->nom_parcours = $ue_ec0->nom_parcours;
            $id_parcours_prec = $ue_ec0->id_parcours;
            $ic_parcours = $ue_ec0->id_parcours;

            $niveaux = [];
            $niveau = new stdClass();
            $niveau->id_niveau = $ue_ec0->id_niveau;
            $niveau->nom_niveau = $ue_ec0->nom_niveau;
            $id_niveau = $ue_ec0->id_niveau;
            $id_niveau_prec = $ue_ec0->id_niveau;

            $ues = [];
            $ue = new stdClass();
            $ue->id_ue = $ue_ec0->id_unite_enseignement;
            $ue->nom_ue = $ue_ec0->nom_unite_enseignement;
            $id_ue = $ue_ec0->id_unite_enseignement;
            $id_ue_prec = $ue_ec0->id_unite_enseignement;
            $ecs = [];
            $ec;


            foreach($liste_ue_ec as $ue_ec){
                $id_session_examen = $ue_ec->id_session_examen;
                $id_mention = $ue_ec->id_mention;
                $id_parcours = $ue_ec->id_parcours;
                $id_niveau = $ue_ec->id_niveau;
                $id_ue = $ue_ec->id_unite_enseignement;


                if($id_ue != $id_ue_prec){
                    $ue->ecs = $ecs;
                    $ues[] = $ue;

                    $ue = new stdClass();
                    $ue->id_ue = $id_ue;
                    $ue->nom_ue = $ue_ec->nom_unite_enseignement;
                    $ecs  = [];
                }

                if($id_niveau != $id_niveau_prec){
                    $niveau->ues = $ues;
                    $niveaux[] = $niveau;

                    $niveau = new stdClass();
                    $niveau->id_niveau = $id_niveau;
                    $niveau->nom_niveau = $ue_ec->nom_niveau;
                    $ues = [];
                }

                if($id_parcours != $id_parcours_prec){
                    $parcour->niveaux = $niveaux;
                    $parcours[] = $parcour ;

                    $parcour = new stdClass();
                    $parcour->id_parcours = $id_parcours;
                    $parcour->nom_parcours = $ue_ec->nom_parcours;
                    $niveaux = [];
                }

                if($id_mention != $id_mention_prec){
                    $mention->parcours = $parcours;
                    $mentions[] = $mention;

                    $mention = new stdClass();
                    $mention->id_mention = $id_mention;
                    $mention->nom_mention = $ue_ec->nom_mention;
                    $parcours = [];
                }

                if($id_session_examen != $id_session_examen_prec){
                    $evaluation->mentions = $mentions;
                    $res[] = $evaluation;

                    $evaluation = new stdClass();
                    $evaluation->id_session_examen = $id_session_examen;
                    $evaluation->nom_session_examen = $ue_ec->nom_session_examen;
                    $mentions = [];


                }

                $ec = new stdClass();
                $ec->id_ec = $ue_ec->id_element_constitutif;
                $ec->id_ue_ec = $ue_ec->id_ue_ec;
                $ec->nom_ec = $ue_ec->nom_element_constitutif;
                $ec->nbr_inscrits = $ue_ec->nbr_inscrits;
                $ecs[] = $ec;



                /*for($i=1; $i<=$ue_ec->nbr_inscrits+$en_plus; $i++){
                    $ue_ec_assoc->barcodes[] = DNS1D::getBarcodeSVG( $ue_ec->id_ue_ec.'-'.$i, 'CODABAR',2.5,35,'black', true);
                }*/
                $res_assoc[strval($ue_ec->id_ue_ec)] = $ue_ec;

                $id_ue_prec = $id_ue;
                $id_niveau_prec =$id_niveau;
                $id_parcours_prec = $id_parcours;
                $id_mention_prec = $id_mention;
                $id_session_examen_prec = $id_session_examen;
            }

            $ue->ecs = $ecs;
            $ues[] = $ue;
            $niveau->ues = $ues;
            $niveaux[] = $niveau;
            $parcour->niveaux = $niveaux;
            $parcours[] = $parcour ;
            $mention->parcours = $parcours;
            $mentions[] = $mention;
            $evaluation->mentions = $mentions;
            $res[] = $evaluation;

            //résultat pour la vue qui liste les ue_ec par eval, mention, parcours, niveau, ue
            //var_dump($res);
            $result[] = $res;
            //résultat pour récupérer ensuite les codes barre selon l'ec selectionné
            $result[] = $res_assoc;
            return $result;

        }
        throw new Exception('La liste des UE et EC n\a pas encore été définie pour l\'AU sélectionnée');

    }

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

                $id_ue_prec = $ue_ec->id_unite_enseignement;
                $id_session_prec = $ue_ec->id_session_examen;

            }
            return $res;
        }
        throw new Exception('Aucune U.E. n\'a encore été enregistrée pour le parcours, le niveau et l\'A.U. sélectionnés.');
    }
}

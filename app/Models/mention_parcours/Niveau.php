<?php

namespace App\Models\mention_parcours;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class Niveau extends Model
{
    use HasFactory;
    protected $table = "niveau";
    protected $primaryKey = "id_niveau";

    public static function check_transfert_autorise($parcours_niveau){
        //vérifier dans la table transferts_autorises ci le couple id_niveau, id_parcours est existant
        $autorisations = DB::select('select * from transferts_autorises where id_parcours = ? and id_niveau = ?',[$parcours_niveau->id_parcours, $parcours_niveau->id_niveau]);
        if(empty($autorisations))
            //var_dump($parcours_niveau);
            throw new Exception('Transfert non autorisé pour le niveau et le parours sélectionnés');
    }

    public static function get_niveau_suivant($id_niveau_precedent, $id_parcours){
        // récupérer le niveau suivant pour le parcours donné
        // et vérifier si pour ce parcours ce niveau est existant

        $niveau_prec = Niveau::find($id_niveau_precedent);
        $niveau_suivant = DB::select('select * from v_parcours_niveau where id_parcours = ? and rang = ?',[$id_parcours, $niveau_prec->rang+1]);
        if(empty($niveau_suivant)){
            throw new Exception ('Il n\'y a pas de niveau supérieur à '.$niveau_prec->nom_niveau.' qui soit disponible dans le parcours sélectionné.');
        }
        return $niveau_suivant[0];
    }
}
